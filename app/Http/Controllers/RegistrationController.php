<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationConfirmedMail;
use App\Mail\NewRegistrationNotificationMail;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;

class RegistrationController extends Controller
{
    // Show the registration form
    public function create(Event $event)
    {
        $event->load(['sessions' => fn ($q) => $q->orderBy('session_date', 'asc')]);
        return view('events.register', compact('event'));
    }

    // Handle registration (free or paid → Stripe)
    public function store(Request $request, Event $event)
    {
        $event->load('sessions');

        $baseRules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email',
            'mobile'        => 'nullable|string|max:30',
            'session_ids'   => 'required|array|min:1',
            'session_ids.*' => 'integer|exists:event_sessions,id',
        ];

        $isPaid = ($event->ticket_cost ?? 0) > 0;

        // Extra rules depending on free vs paid
        $extraRules = $isPaid
            ? ['quantity' => 'required|integer|min:1|max:10']
            : [
                'party_adults'   => 'nullable|integer|min:0|max:20',
                'party_children' => 'nullable|integer|min:0|max:20',
            ];

        $validated = $request->validate($baseRules + $extraRules);

        // Only allow sessions that belong to this event
        $validSessionIds = $event->sessions()
            ->whereIn('id', $validated['session_ids'])
            ->pluck('id')
            ->all();

        if (empty($validSessionIds)) {
            return back()
                ->withErrors(['session_ids' => 'Please select at least one valid session for this event.'])
                ->withInput();
        }

        // Prevent duplicate (same event) by user or email
        $already = EventRegistration::where('event_id', $event->id)
            ->where(function ($q) use ($validated) {
                if (Auth::check()) $q->orWhere('user_id', Auth::id());
                $q->orWhere('email', $validated['email']);
            })
            ->exists();

        if ($already) {
            return back()
                ->withErrors(['email' => 'You are already registered for this event.'])
                ->withInput();
        }

        // Prepare values
        $quantity       = $isPaid ? (int) $validated['quantity'] : 1;
        $partyAdults    = $isPaid ? 0 : (int) ($validated['party_adults'] ?? 0);
        $partyChildren  = $isPaid ? 0 : (int) ($validated['party_children'] ?? 0);

        $registration = EventRegistration::create([
            'event_id'       => $event->id,
            'user_id'        => Auth::id(),
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'mobile'         => $validated['mobile'] ?? null,
            'status'         => $isPaid ? 'pending' : 'free',
            'amount'         => $isPaid ? (($event->ticket_cost ?? 0) * $quantity) : 0, // major units
            'quantity'       => $quantity,
            'party_adults'   => $partyAdults,
            'party_children' => $partyChildren,
        ]);

        $registration->sessions()->sync($validSessionIds);

        // Notify organizer (free or paid attempt)
        if ($event->user?->email) {
            Mail::to($event->user->email)->send(
                new NewRegistrationNotificationMail($event, $registration)
            );
        }

        // ---- FREE EVENTS → straight to result
        if (! $isPaid) {
            Mail::to($registration->email)->send(
                new RegistrationConfirmedMail($event, $registration)
            );

            return redirect()->to(
                route('events.register.result', ['event' => $event, 'registered' => 1])
            );
        }

        // ---- PAID EVENTS → Stripe Checkout
        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => ['name' => $event->name],
                    'unit_amount' => (int) round(($event->ticket_cost ?? 0) * 100),
                ],
                'quantity' => $quantity,
            ]],
            'success_url' => route('events.register.result', ['event' => $event, 'paid' => 1]) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('events.register.result', ['event' => $event, 'canceled' => 1]),
            'metadata' => [
                'event_id'        => (string) $event->id,
                'registration_id' => (string) $registration->id,
                'session_ids'     => implode(',', $validSessionIds),
                'email'           => $validated['email'],
                'name'            => $validated['name'],
                'user_id'         => (string) (Auth::id() ?? ''),
                'quantity'        => (string) $quantity,
            ],
        ]);

        $registration->update(['stripe_session_id' => $session->id]);

        return redirect()->away($session->url);
    }


    // NEW: Result page (success/cancel/errors)
    public function result(Request $request, Event $event)
    {
        $event->load('sessions');

        $state = 'info';     // success | error | warning | info
        $title = 'Registration';
        $message = null;

        // 1) Canceled
        if ($request->boolean('canceled')) {
            $state = 'warning';
            $title = 'Checkout cancelled';
            $message = 'No payment was taken. You can try again when ready.';
            return view('events.register-result', compact('event', 'state', 'title', 'message'));
        }

        // 2) Free registration success
        if ($request->boolean('registered')) {
            $state = 'success';
            $title = 'You’re registered! 🎉';
            $message = 'We’ve saved your registration. See you there!';
            return view('events.register-result', compact('event', 'state', 'title', 'message'));
        }

        // 3) Paid registration – verify Stripe session if present
        if ($request->boolean('paid') && $request->filled('session_id')) {
            try {
                $stripe  = new StripeClient(config('services.stripe.secret'));
                $session = $stripe->checkout->sessions->retrieve($request->query('session_id'), []);

                if ($session && $session->payment_status === 'paid') {
                    EventRegistration::where('stripe_session_id', $session->id)
                        ->update([
                            'status' => 'paid',
                            'amount' => (($session->amount_total ?? 0) / 100),
                        ]);

                    $state = 'success';
                    $title = 'Payment successful 🎉';
                    $message = 'Your registration is confirmed.';
                } else {
                    $state = 'error';
                    $title = 'We couldn’t verify your payment';
                    $message = 'If you saw a Stripe success screen, you should be registered. Otherwise, please try again.';
                }
            } catch (\Throwable $e) {
                $state = 'error';
                $title = 'We couldn’t verify your payment';
                $message = 'Please refresh in a moment or contact support if you were charged.';
            }

            return view('events.register-result', compact('event', 'state', 'title', 'message'));
        }

        // 4) Fallback
        $state = 'info';
        $title = 'Status not clear';
        $message = 'If you just completed checkout, please refresh in a moment.';
        return view('events.register-result', compact('event', 'state', 'title', 'message'));
    }
}
