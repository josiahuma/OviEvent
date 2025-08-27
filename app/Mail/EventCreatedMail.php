<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;

class EventCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event) {}

    public function build()
    {
        return $this->subject('Your event has been created ✅')
            ->view('emails.event-created');
    }
}
