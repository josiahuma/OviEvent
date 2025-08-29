<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{-- resources/views/layouts/guest.blade.php --}}
        <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4 py-8">
            <a href="{{ route('homepage') }}" class="mb-6 inline-flex justify-center">
                <img
                    src="{{ asset('images/logo.png') }}"   {{-- update path if different --}}
                    alt="{{ config('app.name', 'ovievent') }}"
                    class="h-10 w-auto max-w-[180px] object-contain select-none"
                    loading="lazy"
                />
            </a>

            <div class="w-full sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
