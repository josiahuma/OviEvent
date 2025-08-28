<p>Hi {{ e($user->name ?? 'there') }},</p>

<p>Welcome to <strong>Ovievent</strong>! Create, share and sell tickets for your events.</p>

<p>
    Get started by creating your first event:
    <a href="{{ route('dashboard') }}">{{ route('dashboard') }}</a>
</p>

<p>If you need a hand, just reply to this email.</p>
