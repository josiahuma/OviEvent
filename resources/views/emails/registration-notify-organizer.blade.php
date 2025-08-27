<p>New registration for <strong>{{ $event->name }}</strong>.</p>
<p>Name: {{ $registration->name }}<br>
Email: {{ $registration->email }}</p>
<p>Manage registrants: {{ route('events.registrants', $event->id) }}</p>
