<p>Hi {{ $user->name ?? 'there' }},</p>
<p>Welcome to <strong>Ovievent</strong>! Create, share and sell tickets for your events.</p>
<p>Get started by creating your first event: {{ route('dashboard') }}</p>
