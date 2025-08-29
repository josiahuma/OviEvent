<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUnlock;
use App\Models\EventPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class RegistrantsController extends Controller
{
    private int $unlockAmount = 50; // £9.00 to unlock free-event registrants (minor units)
    private string $currency = 'gbp';

    public function index(Event $event)
    {
        abort_unless($event->user_id === Auth::id(), 403);

        $isPaidEvent = ($event->ticket_cost ?? 0) > 0;

        // Free events require unlock unless already unlocked
        $isUnlocked = EventUnlock::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('unlocked_at')
            ->exists();

        if (!$isPaidEvent && !$isUnlocked) {
            // pass the *model* so route uses public_id
            return redirect()->route('events.registrants.unlock', $event)
                ->with('error', 'Unlock registrant details for this free event.');
        }

        // Load registrations & sessions
        $event->load(['registrations.sessions' => fn ($q) => $q->orderBy('session_date')]);

        // Earnings math (minor units)
        $paidRegs = $event->registrations->filter(function ($r) use ($event) {
            if (($event->ticket_cost ?? 0) <= 0) return false;

            $status = isset($r->status) ? strtolower((string) $r->status) : null;
            if ($status === 'paid') return true;

            // Optional fallback — treat having a Stripe session as paid
            if (!empty($r->stripe_session_id)) return true;

            return false;
        });

        $sumMinor = $paidRegs->sum(function ($r) use ($event) {
            if (isset($r->amount) && is_numeric($r->amount) && (float) $r->amount > 0) {
                return (int) round(((float) $r->amount) * 100);
            }
            return (int) round(((float) ($event->ticket_cost ?? 0)) * 100);
        });

        $commissionMinor = (int) round($sumMinor * 0.20);
        $payoutMinor     = max(0, $sumMinor - $commissionMinor);

        $hasProcessingPayout = EventPayout::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->where('status', 'processing')
            ->exists();

        return view('registrants.index', [
            'event'               => $event,
            'isPaidEvent'         => $isPaidEvent,
            'sumMinor'            => $sumMinor,
            'commissionMinor'     => $commissionMinor,
            'payoutMinor'         => $payoutMinor,
            'currency'            => 'GBP',
            'hasProcessingPayout' => $hasProcessingPayout,
        ]);
    }

    public function unlock(Event $event)
    {
        abort_unless($event->user_id === Auth::id(), 403);
        if (($event->ticket_cost ?? 0) > 0) {
            return redirect()->route('events.registrants', $event);
        }

        $already = EventUnlock::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('unlocked_at')
            ->first();

        if ($already) {
            return redirect()->route('events.registrants', $event)->with('success', 'Registrants already unlocked.');
        }

        return view('registrants.unlock', [
            'event'   => $event,
            'amount'  => $this->unlockAmount,
            'currency'=> strtoupper($this->currency),
        ]);
    }

    public function checkout(Request $request, Event $event)
    {
        abort_unless($event->user_id === Auth::id(), 403);
        if (($event->ticket_cost ?? 0) > 0) {
            return redirect()->route('events.registrants', $event);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $this->currency,
                    'product_data' => [
                        'name'        => 'Unlock registrant details',
                        'description' => 'One-time unlock for event: ' . $event->name,
                    ],
                    'unit_amount' => $this->unlockAmount,
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'purpose'  => 'registrants_unlock',
                'event_id' => (string) $event->id,   // keep internal numeric ID in metadata
                'user_id'  => (string) Auth::id(),
            ],
            // pass the *model* so URLs use public_id
            'success_url' => route('events.registrants.unlock.success', $event) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('events.registrants.unlock', $event),
        ]);

        EventUnlock::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => Auth::id()],
            ['stripe_session_id' => $session->id, 'amount' => $this->unlockAmount, 'currency' => $this->currency]
        );

        return redirect()->away($session->url);
    }

    public function success(Request $request, Event $event)
    {
        abort_unless($event->user_id === Auth::id(), 403);

        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('events.registrants.unlock', $event)->with('error', 'Missing session id.');
        }

        $stripe  = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId, []);

        if (!$session || $session->payment_status !== 'paid') {
            return redirect()->route('events.registrants.unlock', $event)->with('error', 'Payment not completed.');
        }

        EventUnlock::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => Auth::id()],
            [
                'stripe_session_id'        => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'amount'                   => $this->unlockAmount,
                'currency'                 => $this->currency,
                'unlocked_at'              => now(),
            ]
        );

        return redirect()->route('events.registrants', $event)->with('success', 'Registrants unlocked.');
    }
}
