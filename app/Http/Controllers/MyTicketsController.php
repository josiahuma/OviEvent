<?php

namespace App\Http\Controllers;

use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyTicketsController extends Controller
{
    /**
     * Statuses that count as a completed/active paid registration.
     */
    private array $paidDone = ['paid', 'complete', 'completed', 'succeeded'];

    /**
     * Returns a closure that limits visible rows:
     *  - status is NULL (legacy) OR in ['free', ...$paidDone] (case-insensitive)
     *  - hides pending / canceled / cancelled
     */
    private function visibleStatusesConstraint(): \Closure
    {
        // include a case-variant set for simple DB filtering
        $allowed = array_unique(array_merge(
            ['free'],
            $this->paidDone,
            array_map('ucfirst', array_merge(['free'], $this->paidDone))
        ));

        return function ($q) use ($allowed) {
            $q->whereNull('status')
              ->orWhereIn('status', $allowed);
        };
    }

    public function index()
    {
        $user = Auth::user();

        $registrations = EventRegistration::with([
                'event.sessions' => fn ($q) => $q->orderBy('session_date'),
                'sessions'       => fn ($q) => $q->orderBy('session_date'),
            ])
            // (user-owned) OR (guest by email) — wrapped so the status filter applies to both
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere(function ($q2) use ($user) {
                      $q2->whereNull('user_id')->where('email', $user->email);
                  });
            })
            // only show free or completed/paid
            ->where($this->visibleStatusesConstraint())
            ->orderByDesc('created_at')
            ->get();

        return view('my-tickets.index', compact('registrations'));
    }

    public function edit($registrationId)
    {
        $user = Auth::user();

        $registration = EventRegistration::with([
                'event.sessions' => fn ($q) => $q->orderBy('session_date'),
                'sessions'       => fn ($q) => $q->orderBy('session_date'),
            ])->findOrFail($registrationId);

        // security: must belong to user (or be a guest reg for this email)
        if (!($registration->user_id === $user->id ||
              ($registration->user_id === null && $registration->email === $user->email))) {
            abort(403);
        }

        $event   = $registration->event;
        $isPaid  = ($event->ticket_cost ?? 0) > 0;
        $status  = strtolower((string) $registration->status);

        // hide pending/canceled paid items from edit (defensive in case of deep link)
        if ($isPaid && !in_array($status, $this->paidDone, true)) {
            return redirect()->route('my.tickets.index')
                ->with('error', 'This ticket is not active yet.');
        }

        return view('my-tickets.edit', [
            'registration' => $registration,
            'event'        => $event,
            'isPaid'       => $isPaid,
        ]);
    }

    public function update(Request $request, $registrationId)
    {
        $user = Auth::user();

        $registration = EventRegistration::with(['event', 'event.sessions'])
            ->findOrFail($registrationId);

        // security: must belong to user (or be a guest reg for this email)
        if (!($registration->user_id === $user->id ||
              ($registration->user_id === null && $registration->email === $user->email))) {
            abort(403);
        }

        $event   = $registration->event;
        $isPaid  = ($event->ticket_cost ?? 0) > 0;
        $status  = strtolower((string) $registration->status);

        // same defensive guard on update
        if ($isPaid && !in_array($status, $this->paidDone, true)) {
            return redirect()->route('my.tickets.index')
                ->with('error', 'This ticket is not active yet.');
        }

        // base rules
        $rules = ['email' => 'required|email'];

        // free events: allow party + session edits
        if (!$isPaid) {
            $rules['party_adults']   = 'nullable|integer|min:0|max:20';
            $rules['party_children'] = 'nullable|integer|min:0|max:20';
            $rules['session_ids']    = 'required|array|min:1';
            $rules['session_ids.*']  = 'integer|exists:event_sessions,id';
        }

        $data = $request->validate($rules);

        // prevent duplicate email on same event (except self)
        $emailInUse = EventRegistration::where('event_id', $event->id)
            ->where('email', $data['email'])
            ->where('id', '!=', $registration->id)
            ->exists();

        if ($emailInUse) {
            return back()->withErrors([
                'email' => 'That email is already used for this event by another registration.',
            ])->withInput();
        }

        // build update payload
        $update = ['email' => $data['email']];

        if (!$isPaid) {
            $update['party_adults']   = (int) ($data['party_adults'] ?? 0);
            $update['party_children'] = (int) ($data['party_children'] ?? 0);

            // only keep sessions that belong to this event
            $validSessionIds = $event->sessions()
                ->whereIn('id', $data['session_ids'] ?? [])
                ->pluck('id')
                ->all();

            if (empty($validSessionIds)) {
                return back()->withErrors(['session_ids' => 'Please select at least one valid session.'])
                    ->withInput();
            }

            $registration->sessions()->sync($validSessionIds);
        }

        // claim guest reg to this user if it was guest
        if ($registration->user_id === null) {
            $update['user_id'] = $user->id;
        }

        $registration->update($update);

        return back()->with('success', 'Your ticket details have been updated.');
    }
}
