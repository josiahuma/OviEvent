<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    // Show the registration form
    public function create($eventId)
    {
        $event = Event::with(['sessions' => fn($q) => $q->orderBy('session_date', 'asc')])->findOrFail($eventId);
        return view('events.register', compact('event'));
    }

    // Handle registration (free or paid → Stripe)
    public function store(Request $request, $eventId)
    {
        $event = Event::with('sessions')->findOrFail($eventId);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email',
            'mobile'       => 'nullable|string|max:30',
            'session_ids'  => 'required|array|min:1',
            'session_ids.*'=> 'integer|exists:event_sessions,id',
        ]);

        // Only allow sessions that belong to this event
        $validSessionIds = $event->sessions()
            ->whereIn('id', $validated['session_ids'])
            ->pluck('id')
            ->all();

        if (empty($validSessionIds)) {
            return back()->withErrors(['session_ids' => 'Please select at least one valid session for this event.'])->withInput();
        }

        // Prevent duplicate (same event) by user or email
        $already = EventRegistration::where('event_id', $event->id)
            ->where(function ($q) use ($validated) {
                if (Auth::check()) {
                    $q->orWhere('user_id', Auth::id());
                }
                $q->orWhere('email', $validated['email']);
            })
            ->exists();

        if ($already) {
            return back()->withErrors(['email' => 'You are already registered for this event.'])->withInput();
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

        // Free events: done
        if (!$isPaid) {
            return redirect()->route('events.show', $event->id)
                ->with('success', 'Registration confirmed. See you there!');
        }

        // Paid events: Stripe Checkout
        // Ensure: composer require stripe/stripe-php
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
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
            'success_url' => route('events.show', $event->id) . '?paid=1&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('events.register.create', $event->id) . '?canceled=1',
            'metadata' => [
                'event_id' => (string)$event->id,
                'registration_id' => (string)$registration->id,
                'session_ids' => implode(',', $validSessionIds),
                'email' => $validated['email'],
                'name' => $validated['name'],
                'user_id' => (string)(Auth::id() ?? ''),
            ],
        ]);

        $registration->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }
}
