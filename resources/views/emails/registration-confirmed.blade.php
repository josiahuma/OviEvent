<p>Hi {{ $registration->name ?? 'there' }},</p>

<p>Your registration for <strong>{{ $event->name }}</strong> is confirmed.</p>

@php
    // Ensure we show the registrant's selected sessions
    $chosen = $registration->relationLoaded('sessions')
        ? $registration->sessions
        : $registration->sessions()->get();
@endphp

@if($chosen->count())
    <p>
        Sessions:
        {{ $chosen->sortBy('session_date')->map(function ($s) {
            return $s->session_name.' ('.\Carbon\Carbon::parse($s->session_date)->format('D, d M Y · g:ia').')';
        })->join(', ') }}
    </p>
@endif

<p>
    See event:
    <a href="{{ route('events.show', $event) }}">{{ route('events.show', $event) }}</a>
</p>

<p>
    Why not consider signing up for an OviEvent account? It makes managing your events/tickets easier.
    <a href="{{ route('register') }}">Create an account</a>
</p>