<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationConfirmedMail;

class RegistrationController extends Controller
{
    // Show the registration form
    public function create($eventId)
    {
        $event = Event::with(['sessions' => fn($q) => $q->orderBy('session_date', 'asc')])
            ->findOrFail($eventId);

        return view('events.register', compact('event'));
    }

    // Handle registration (free or paid → Stripe)
    public function store(Request $request, $eventId)
    {
        $event = Event::with('sessions')->findOrFail($eventId);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email',
            'mobile'        => 'nullable|string|max:30',
            'session_ids'   => 'required|array|min:1',
            'session_ids.*' => 'integer|exists:event_sessions,id',
        ]);

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

        $isPaid = ($event->ticket_cost ?? 0) > 0;

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id'  => Auth::id(),
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile'   => $validated['mobile'] ?? null,
            'status'   => $isPaid ? 'pending' : 'free',
            'amount'   => $event->ticket_cost ?? 0,
        ]);

        $registration->sessions()->sync($validSessionIds);
        // Notify organizer immediately about a new registration (free or paid-started)
        if ($event->user?->email) {
            Mail::to($event->user->email)->send(new NewRegistrationNotificationMail($event, $registration));
        }


        // ---- FREE EVENTS → go straight to the "result" page
        if (! $isPaid) {
            // FREE: attendee confirmation now
            Mail::to($registration->email)->send(new RegistrationConfirmedMail($event, $registration));
            return redirect()->route('events.register.result', [
                'id'         => $event->id,
                'registered' => 1,  // query flag so the page knows what to show
            ]);
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
                'quantity' => 1,
            ]],
            'success_url' => route('events.register.result', ['id' => $event->id]) . '?paid=1&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('events.register.result', ['id' => $event->id]) . '?canceled=1',
            'metadata' => [
                'event_id'        => (string) $event->id,
                'registration_id' => (string) $registration->id,
                'session_ids'     => implode(',', $validSessionIds),
                'email'           => $validated['email'],
                'name'            => $validated['name'],
                'user_id'         => (string) (Auth::id() ?? ''),
            ],
        ]);

        $registration->update(['stripe_session_id' => $session->id]);

        return redirect()->away($session->url);
    }

    // NEW: Result page (success/cancel/errors) – mobile friendly and reliable
    public function result(Request $request, $eventId)
    {
        $event = Event::with('sessions')->findOrFail($eventId);

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

        // 2) Free registration success (no Stripe)
        if ($request->boolean('registered')) {
            $state = 'success';
            $title = 'You’re registered! 🎉';
            $message = 'We’ve saved your registration. See you there!';
            return view('events.register-result', compact('event', 'state', 'title', 'message'));
        }

        // 3) Paid registration – verify Stripe session if present
        if ($request->boolean('paid') && $request->filled('session_id')) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));
                $session = $stripe->checkout->sessions->retrieve($request->query('session_id'), []);

                if ($session && $session->payment_status === 'paid') {
                    // Mark registration paid (if we can find it)
                    EventRegistration::where('stripe_session_id', $session->id)
                        ->update(['status' => 'paid', 'amount' => (($session->amount_total ?? 0) / 100)]);

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

        // 4) Fallback – unknown state
        $state = 'info';
        $title = 'Status not clear';
        $message = 'If you just completed checkout, please refresh in a moment.';
        return view('events.register-result', compact('event', 'state', 'title', 'message'));
    }
}
