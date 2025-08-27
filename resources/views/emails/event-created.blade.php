<p>Hi {{ $event->organizer ?? $event->user->name ?? 'there' }},</p>
<p>Your event <strong>{{ $event->name }}</strong> has been created.</p>
<p>View it here: {{ route('events.show', $event->id) }}</p>
<p>You can manage your event and tickets here: {{ route('events.manage', $event->id) }}</p>
<p>Share your event and start selling tickets!</p>