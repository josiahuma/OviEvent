{{-- resources/views/components/application-logo.blade.php --}}
@props(['class' => 'block h-9 w-auto'])

<img {{ $attributes->merge(['class' => $class]) }}
  src="{{ asset('images/logo.png') }}"
  alt="{{ config('app.name') }} logo"
/>
