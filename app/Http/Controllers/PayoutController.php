<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = EventPayout::where('user_id', Auth::id())
            ->with('event:id,name')
            ->latest()->paginate(12);

        return view('payouts.index', compact('payouts'));
    }

    public function create(Event $event, Request $request)
    {
        abort_unless($event->user_id === Auth::id(), 403);

        // Amount is passed in query (?amount=1234), fallback to 0
        $amount = (int) $request->query('amount', 0);
        $currency = 'GBP';

        // Prevent multiple processing payouts for same event
        $hasProcessing = EventPayout::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->where('status', 'processing')
            ->exists();

        if ($hasProcessing) {
            return redirect()->route('payouts.index')->with('error', 'You already have a payout processing for this event.');
        }

        return view('payouts.create', compact('event','amount','currency'));
    }

    public function store(Event $event, Request $request)
    {
        abort_unless($event->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'amount' => 'required|integer|min:1', // minor units
            'account_name' => 'required|string|max:100',
            'sort_code' => 'required|string|max:16',
            'account_number' => 'required|string|max:20',
            'iban' => 'nullable|string|max:34',
        ]);

        // Prevent multiple processing payouts for same event
        $exists = EventPayout::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->where('status', 'processing')
            ->exists();

        if ($exists) {
            return redirect()->route('payouts.index')->with('error', 'You already have a payout processing for this event.');
        }

        EventPayout::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'amount' => $validated['amount'],
            'currency' => 'gbp',
            'account_name' => $validated['account_name'],
            'sort_code' => $validated['sort_code'],
            'account_number' => $validated['account_number'],
            'iban' => $validated['iban'] ?? null,
            'status' => 'processing',
        ]);

        return redirect()->route('payouts.index')
            ->with('success', 'Payout request submitted and marked as processing.');
    }
}
