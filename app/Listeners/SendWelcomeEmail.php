<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        if (!$event->user?->email) return;
        Mail::to($event->user->email)->send(new WelcomeUserMail($event->user));
    }
}
