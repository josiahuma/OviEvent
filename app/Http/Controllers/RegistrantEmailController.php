<?php

namespace App\Http\Controllers;

use App\Mail\RegistrantBulkMail;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        $registrants = $event->registrations()
            ->select('email')
            ->whereNotNull('email')
            ->pluck('email')
            ->unique()
            ->values();

        if ($registrants->isEmpty()) {
            return back()->with('error', 'No registrants with email.');
        }

        // Send individually to each (avoids exposing addresses)
        foreach ($registrants as $email) {
            Mail::to($email)->send(new RegistrantBulkMail(
                $event,
                $validated['subject'],
                nl2br(e($validated['message']))
            ));
        }

        return redirect()->route('events.registrants', $event->id)
            ->with('success', 'Your message is being sent to registrants.');
    }
}
