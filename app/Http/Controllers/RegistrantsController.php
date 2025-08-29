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
    // £9.99 to unlock free-event registrants (minor units in GBP)
    private int $unlockAmount = 999;
    private string $currency = 'gbp';

    public function index(Event $event)
    {
        abort_unless($event->user_id === Auth::id(), 403);

        $isPaidEvent = ($event->ticket_cost ?? 0) > 0;

        $isUnlocked = EventUnlock::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('unlocked_at')
            ->exists();

        if (!$isPaidEvent && !$isUnlocked) {
            return redirect()->route('events.registrants.unlock', $event)
                ->with('error', 'Unlock registrant details for this free event.');
        }

        // Only PAID rows for paid events
        $event->load([
            'registrations' => function ($q) use ($isPaidEvent) {
                if ($isPaidEvent) $q->where('status', 'paid');
            },
            'registrations.sessions' => fn($q) => $q->orderBy('session_date'),
        ]);

        // sums (minor units)
        $sumMinor = $event->registrations->sum(function ($r) {
            return (int) round(((float) ($r->amount ?? 0)) * 100);
        });
        $commissionMinor = (int) round($sumMinor * 0.20);
        $payoutMinor     = max(0, $sumMinor - $commissionMinor);

        $hasProcessingPayout = EventPayout::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->where('status', 'processing')
            ->exists();

        $currency = strtoupper($event->ticket_currency ?? 'GBP');
        $symbols  = ['GBP'=>'£','USD'=>'$','EUR'=>'€','NGN'=>'₦','KES'=>'KSh','GHS'=>'₵','ZAR'=>'R'];
        $symbol   = $symbols[$currency] ?? ($currency.' ');

        return view('registrants.index', [
            'event'               => $event,
            'isPaidEvent'         => $isPaidEvent,
            'sumMinor'            => $sumMinor,
            'commissionMinor'     => $commissionMinor,
            'payoutMinor'         => $payoutMinor,
            'currency'            => $currency,
            'symbol'              => $symbol,
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
            'event'    => $event,
            'amount'   => $this->unlockAmount,   // minor units
            'currency' => strtoupper($this->currency),
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
                    'currency' => $this->currency, // GBP for unlocks
                    'product_data' => [
                        'name'        => 'Unlock registrant details',
                        'description' => 'One-time unlock for event: ' . $event->name,
                    ],
                    'unit_amount' => $this->unlockAmount, // minor units
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'purpose'  => 'registrants_unlock',
                'event_id' => (string) $event->id,
                'user_id'  => (string) Auth::id(),
            ],
            'success_url' => route('events.registrants.unlock.success', $event) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('events.registrants.unlock', $event),
        ]);

        EventUnlock::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => Auth::id()],
            [
                'stripe_session_id' => $session->id,
                'amount'            => $this->unlockAmount, // minor units
                'currency'          => $this->currency,
            ]
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

        // Persist the actual paid amount/currency from Stripe (minor units)
        $paidMinor = (int) ($session->amount_total ?? $this->unlockAmount);
        $paidCurr  = strtolower((string) ($session->currency ?? $this->currency));

        EventUnlock::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => Auth::id()],
            [
                'stripe_session_id'        => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'amount'                   => $paidMinor, // minor units
                'currency'                 => $paidCurr,
                'unlocked_at'              => now(),
            ]
        );

        return redirect()->route('events.registrants', $event)->with('success', 'Registrants unlocked.');
    }

    /**
     * Normalize currency code to 3-letter lowercase (defaults to gbp).
     */
    private function normalizeCurrency(?string $code): string
    {
        $c = strtolower(trim((string) $code));
        return preg_match('/^[a-z]{3}$/', $c) ? $c : 'gbp';
    }

    /**
     * Number of decimal places for a currency (Stripe minor unit exponent).
     * 0-decimal and 3-decimal currencies handled; default 2.
     */
    private function currencyExponent(string $currency): int
    {
        $c = strtolower($currency);

        // 0-decimal on Stripe
        $zero = [
            'bif','clp','djf','gnf','jpy','kmf','krw','mga',
            'pyg','rwf','ugx','vnd','vuv','xaf','xof','xpf',
        ];
        if (in_array($c, $zero, true)) return 0;

        // 3-decimal currencies
        $three = ['bhd','jod','kwd','omr','tnd'];
        if (in_array($c, $three, true)) return 3;

        return 2;
    }
}
