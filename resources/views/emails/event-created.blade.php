<p>Hi {{ $event->organizer ?: ($event->user?->name ?: 'there') }},</p>

<p>Your event <strong>{{ $event->name }}</strong> has been created.</p>

<p>
    View it here:
    <a href="{{ route('events.show', $event) }}">{{ route('events.show', $event) }}</a>
</p>

<p>
    You can manage your event and tickets here:
    <a href="{{ route('dashboard') }}">{{ route('dashboard') }}</a>
</p>

<p>Share your event and start selling tickets!</p>
