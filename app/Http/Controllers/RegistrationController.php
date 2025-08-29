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

        $extraRules = $isPaid
            ? ['quantity' => 'required|integer|min:1|max:10']
            : [
                'party_adults'   => 'nullable|integer|min:0|max:20',
                'party_children' => 'nullable|integer|min:0|max:20',
            ];

        $validated = $request->validate($baseRules + $extraRules);

        // sessions must belong to this event
        $validSessionIds = $event->sessions()
            ->whereIn('id', $validated['session_ids'])
            ->pluck('id')->all();

        if (!$validSessionIds) {
            return back()->withErrors(['session_ids' => 'Please select at least one valid session for this event.'])->withInput();
        }

        // ---- Only block if user already COMPLETED (paid or free)
        $completedExists = EventRegistration::where('event_id', $event->id)
            ->whereIn('status', ['paid', 'free'])
            ->where(function ($q) use ($validated) {
                if (Auth::check()) $q->orWhere('user_id', Auth::id());
                $q->orWhere('email', $validated['email']);
            })
            ->exists();

        if ($completedExists) {
            return back()->withErrors(['email' => 'You are already registered for this event.'])->withInput();
        }

        // We'll reuse a PENDING row (if any) so retries don’t create duplicates
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('status', 'pending')
            ->where(function ($q) use ($validated) {
                if (Auth::check()) $q->orWhere('user_id', Auth::id());
                $q->orWhere('email', $validated['email']);
            })
            ->latest('id')
            ->first();

        $quantity      = $isPaid ? (int) $validated['quantity'] : 1;
        $partyAdults   = $isPaid ? 0 : (int) ($validated['party_adults'] ?? 0);
        $partyChildren = $isPaid ? 0 : (int) ($validated['party_children'] ?? 0);
        $currency      = strtolower($event->ticket_currency ?? 'gbp');

        if ($registration) {
            // reuse the same row
            $registration->fill([
                'name'           => $validated['name'],
                'email'          => $validated['email'],
                'mobile'         => $validated['mobile'] ?? null,
                'quantity'       => $quantity,
                'party_adults'   => $partyAdults,
                'party_children' => $partyChildren,
                'currency'       => $currency,
                // keep status=pending until Stripe says paid
            ])->save();
        } else {
            // create a fresh row (free → free, paid → pending)
            $registration = EventRegistration::create([
                'event_id'       => $event->id,
                'user_id'        => Auth::id(),
                'name'           => $validated['name'],
                'email'          => $validated['email'],
                'mobile'         => $validated['mobile'] ?? null,
                'status'         => $isPaid ? 'pending' : 'free',
                'amount'         => $isPaid ? (($event->ticket_cost ?? 0) * $quantity) : 0, // major units
                'currency'       => $currency,
                'quantity'       => $quantity,
                'party_adults'   => $partyAdults,
                'party_children' => $partyChildren,
            ]);
        }

        $registration->sessions()->sync($validSessionIds);

        // Organizer email: only for FREE (completed) – NOT for pending paid attempts
        if (! $isPaid && $event->user?->email) {
            \Mail::to($event->user->email)->send(new \App\Mail\NewRegistrationNotificationMail($event, $registration));
        }

        // FREE → done
        if (! $isPaid) {
            \Mail::to($registration->email)->send(new \App\Mail\RegistrationConfirmedMail($event, $registration));
            return redirect()->to(route('events.register.result', ['event' => $event, 'registered' => 1]));
        }

        // PAID → Stripe Checkout
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => $event->name],
                    'unit_amount' => (int) round(($event->ticket_cost ?? 0) * 100),
                ],
                'quantity' => $quantity,
            ]],
            // include session id on BOTH urls so we can mark canceled
            'success_url' => route('events.register.result', ['event' => $event, 'paid' => 1]).'&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('events.register.result', ['event' => $event, 'canceled' => 1]).'&session_id={CHECKOUT_SESSION_ID}',
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
            if ($request->filled('session_id')) {
                EventRegistration::where('stripe_session_id', $request->query('session_id'))
                    ->where('event_id', $event->id)
                    ->update(['status' => 'canceled']);
            }
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
                    // Convert Stripe amount_total (minor units) to major using correct exponent
                    $sessionCurrency = strtolower((string) ($session->currency ?? 'gbp'));
                    $exp             = $this->currencyExponent($sessionCurrency);
                    $amountMajor     = ($session->amount_total ?? 0) / (10 ** $exp);

                    EventRegistration::where('stripe_session_id', $session->id)
                        ->update([
                            'status'   => 'paid',
                            'amount'   => $amountMajor,       // major units
                            'currency' => $sessionCurrency,
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

    /**
     * Normalize currency code to 3-letter lowercase (defaults to gbp).
     */
    private function normalizeCurrency(?string $code): string
    {
        $c = strtolower(trim((string) $code));
        // basic sanity: ensure 3 letters; fallback to gbp
        if (!preg_match('/^[a-z]{3}$/', $c)) {
            return 'gbp';
        }
        return $c;
    }

    /**
     * Return the number of decimal places (exponent) for a currency.
     * Handles zero-decimal and 3-decimal currencies; default is 2.
     */
    private function currencyExponent(string $currency): int
    {
        $c = strtolower($currency);

        // ISO currencies with 0 decimals on Stripe
        $zero = [
            'bif','clp','djf','gnf','jpy','kmf','krw','mga',
            'pyg','rwf','ugx','vnd','vuv','xaf','xof','xpf',
        ];
        if (in_array($c, $zero, true)) {
            return 0;
        }

        // Rare 3-decimal currencies
        $three = ['bhd','jod','kwd','omr','tnd'];
        if (in_array($c, $three, true)) {
            return 3;
        }

        // Default (most currencies)
        return 2;
    }
}
