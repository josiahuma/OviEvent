<p>New registration for <strong>{{ $event->name }}</strong>.</p>

@php
    // --- helpers to mask PII in organizer emails ---
    $maskEmail = function (?string $email) {
        if (! $email || ! str_contains($email, '@')) return 'hidden';
        [$user, $domain] = explode('@', $email, 2);

        // keep first 2 chars of user, mask the rest
        $userKeep  = mb_substr($user, 0, 2);
        $userMask  = $userKeep . str_repeat('•', max(0, mb_strlen($user) - mb_strlen($userKeep)));

        // mask domain but keep TLD (e.g. ".com")
        $parts = explode('.', $domain);
        $tld   = array_pop($parts);
        $domMaskLen = max(3, mb_strlen(implode('.', $parts)) ?: 3);
        $domainMask = str_repeat('•', $domMaskLen) . '.' . $tld;

        return $userMask . '@' . $domainMask;
    };

    $maskMobile = function (?string $mobile) {
        if (! $mobile) return null;
        $digits = preg_replace('/\D+/', '', $mobile);
        if ($digits === '') return 'hidden';
        $last3  = mb_substr($digits, -3);
        return str_repeat('•', max(0, mb_strlen($digits) - 3)) . $last3;
    };

    $maskedEmail  = $maskEmail($registration->email ?? null);
    $maskedMobile = $maskMobile($registration->mobile ?? null);
@endphp

<p>
    Name: {{ $registration->name }}<br>
    Email: {{ $maskedEmail }} <em>(unlock to view)</em>
    @if(!empty($registration->mobile))
        <br>Mobile: {{ $maskedMobile }} <em>(unlock to view)</em>
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
