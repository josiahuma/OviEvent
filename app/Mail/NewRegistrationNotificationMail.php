<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;
use App\Models\EventRegistration;

class NewRegistrationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event, public EventRegistration $registration) {}

    public function build()
    {
        return $this->subject('New registration for your event')
            ->view('emails.registration-notify-organizer');
    }
}
