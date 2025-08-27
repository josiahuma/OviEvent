<p>Hi {{ $registration->name }},</p>
<p>Your registration for <strong>{{ $event->name }}</strong> is confirmed.</p>
@if($event->sessions->count())
<p>Sessions: {{ $event->sessions->pluck('session_name')->join(', ') }}</p>
@endif
<p>See event: {{ route('events.show', $event->id) }}</p>
