<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketManageLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;
    public EventRegistration $registration;
    public string $link;

    public function __construct(Event $event, EventRegistration $registration, string $link)
    {
        $this->event = $event;
        $this->registration = $registration;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Manage your booking for '.$this->event->name)
            ->view('emails.tickets.manage-link');
    }
}
