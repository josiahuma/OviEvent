<?php

namespace App\Http\Controllers;

use App\Mail\RegistrantBulkMail;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RegistrantEmailController extends Controller
{
    public function create(Event $event)
    {
        abort_unless($event->user_id === Auth::id(), 403);

        $count = $event->registrations()->count();

        return view('registrants.email', [
            'event' => $event,
            'count' => $count,
        ]);
    }

    public function send(Request $request, Event $event)
    {
        abort_unless($event->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:10000',
        ]);

        // Unique, non-empty emails only
        $emails = $event->registrations()
            ->pluck('email')
            ->filter()        // remove null/empty
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            return back()->with('error', 'No registrants with email.');
        }

        // Send one-by-one (keeps recipients private).
        // If you enable queues later, swap ->send() for ->queue().
        foreach ($emails as $to) {
            Mail::to($to)->send(new RegistrantBulkMail(
                $event,
                $validated['subject'],
                nl2br(e($validated['message'])) // simple HTML body
            ));
        }

        // IMPORTANT: pass the *model* so the route uses public_id
        return redirect()
            ->route('events.registrants', $event)
            ->with('success', 'Your message has been sent to registrants.');
    }
}
