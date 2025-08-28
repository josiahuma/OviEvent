<p>New registration for <strong>{{ $event->name }}</strong>.</p>

<p>
    Name: {{ $registration->name }}<br>
    Email: <a href="mailto:{{ $registration->email }}">{{ $registration->email }}</a>
    @if(!empty($registration->mobile))
        <br>Mobile: {{ $registration->mobile }}
    @endif
</p>

@php
    $chosen = $registration->relationLoaded('sessions')
        ? $registration->sessions
        : $registration->sessions()->get();
@endphp

@if($chosen->count())
    <p>
        Sessions:<br>
        {!! $chosen->sortBy('session_date')->map(function ($s) {
            return e($s->session_name).' ('.\Carbon\Carbon::parse($s->session_date)->format('D, d M Y · g:ia').')';
        })->implode('<br>') !!}
    </p>
@endif

<p>
    Status: {{ ucfirst($registration->status ?? 'pending') }}
    @if(is_numeric($registration->amount) && $registration->amount > 0)
        — £{{ number_format((float) $registration->amount, 2) }}
    @endif
</p>

<p>
    Manage registrants:
    <a href="{{ route('events.registrants', $event) }}">{{ route('events.registrants', $event) }}</a>
</p>

<p>
    View event:
    <a href="{{ route('events.show', $event) }}">{{ route('events.show', $event) }}</a>
</p>
