<?php

namespace App\Http\Controllers;

use App\Mail\TicketManageLinkMail;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class TicketLookupController extends Controller
{
    public function showForm(Event $event)
    {
        return view('tickets.find', compact('event'));
    }

    public function sendLink(Request $request, Event $event)
    {
        $data = $request->validate(['email' => 'required|email']);

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('email', $data['email'])
            ->first();

        if (! $registration) {
            return back()
                ->withErrors(['email' => 'We couldn’t find a booking with that email for this event.'])
                ->withInput();
        }

        $link = URL::temporarySignedRoute(
            'events.ticket.edit',
            now()->addMinutes(30),
            ['event' => $event, 'reg' => $registration->id]
        );

        Mail::to($registration->email)->send(
            new TicketManageLinkMail($event, $registration, $link)
        );

        return back()->with('success', 'We’ve emailed you a secure link to manage your booking. The link expires in 30 minutes.');
    }

    public function edit(Request $request, Event $event, $reg)
    {
        abort_unless($request->hasValidSignature(), 403);

        $registration = EventRegistration::where('event_id', $event->id)
            ->findOrFail($reg);

        $isFreeEvent = ($event->ticket_cost ?? 0) == 0;

        return view('tickets.edit', [
            'event'        => $event,
            'registration' => $registration,
            'isFreeEvent'  => $isFreeEvent,
        ]);
    }

    public function update(Request $request, Event $event, $reg)
    {
        abort_unless($request->hasValidSignature(), 403);

        $registration = EventRegistration::where('event_id', $event->id)
            ->findOrFail($reg);

        $rules = ['email' => 'required|email'];
        if (($event->ticket_cost ?? 0) == 0) {
            $rules['party_adults']   = 'nullable|integer|min:0|max:20';
            $rules['party_children'] = 'nullable|integer|min:0|max:20';
        }

        $data = $request->validate($rules);

        // Prevent duplicate email on the same event (except the same row)
        $emailInUse = EventRegistration::where('event_id', $event->id)
            ->where('email', $data['email'])
            ->where('id', '!=', $registration->id)
            ->exists();

        if ($emailInUse) {
            return back()
                ->withErrors(['email' => 'Someone has already registered with this email for this event.'])
                ->withInput();
        }

        $update = ['email' => $data['email']];

        if (($event->ticket_cost ?? 0) == 0) {
            $update['party_adults']   = (int) ($data['party_adults'] ?? 0);
            $update['party_children'] = (int) ($data['party_children'] ?? 0);
        }

        $registration->update($update);

        return back()->with('success', 'Your booking has been updated.');
    }
}
