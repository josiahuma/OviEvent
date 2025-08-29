<p>Hi {{ $registration->name ?? 'there' }},</p>

<p>Use the secure link below to manage your booking for <strong>{{ $event->name }}</strong>.
The link expires in 30 minutes.</p>

<p><a href="{{ $link }}">{{ $link }}</a></p>

<p>You can update your email address and, for free events, add the number of adults and children attending with you.</p>

<p>Thanks,<br>{{ config('app.name') }}</p>
