<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;

class RegistrantBulkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event, public string $subjectLine, public string $messageHtml) {}

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.bulk-registrants')
            ->with(['messageHtml' => $this->messageHtml]);
    }
}
