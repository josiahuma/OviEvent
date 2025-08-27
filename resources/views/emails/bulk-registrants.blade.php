<p>Message from {{ $event->organizer ?? 'the organizer' }} regarding <strong>{{ $event->name }}</strong>:</p>
<hr>
{!! $messageHtml !!}
<hr>
<p style="font-size:12px;color:#666">You received this because you registered for {{ $event->name }}.</p>
<p style="font-size:12px;color:#666">To unsubscribe from future emails about this event, please contact the event organizer at {{ $event->organizer_email }}.</p>