<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Ovievent') }}</title>

        {{-- DEFAULT Open Graph / Twitter (page can override in @section('meta')) --}}
        @php
            $siteName   = config('app.name', 'ovievent');
            $baseUrl    = config('app.url') ?: url('/');
            $defaultImg = asset('images/og-default.jpg'); 
        @endphp

        @section('meta')
            <meta property="og:type" content="website">
            <meta property="og:site_name" content="{{ $siteName }}">
            <meta property="og:title" content="{{ $siteName }}">
            <meta property="og:description" content="Create, share and sell tickets for events.">
            <meta property="og:url" content="{{ url()->current() }}">
            <meta property="og:image" content="{{ $defaultImg }}">
            <meta property="og:image:secure_url" content="{{ $defaultImg }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $siteName }}">
            <meta name="twitter:description" content="Create, share and sell tickets for events.">
            <meta name="twitter:image" content="{{ $defaultImg }}">
        @show

        <!-- Tom Select CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            {{-- GLOBAL FLASH TOASTS (mobile-safe) --}}
            @if (session('success') || session('error') || request()->boolean('paid') || request()->boolean('canceled') || request()->boolean('registered'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 6000)"
                    class="fixed inset-x-4 top-[72px] sm:top-4 z-[10000] space-y-2 pointer-events-none"
                    role="status"
                    aria-live="polite"
                >
                    @if (session('success'))
                        <div class="pointer-events-auto rounded-lg border border-green-200 bg-green-50 text-green-800 shadow p-3 flex items-start justify-between gap-3">
                            <div class="text-sm font-medium">{{ session('success') }}</div>
                            <button type="button" class="text-green-700 hover:text-green-900" @click="show = false" aria-label="Close">✕</button>
                        </div>
                    @endif

                    @if (request()->boolean('paid'))
                        <div class="pointer-events-auto rounded-lg border border-green-200 bg-green-50 text-green-800 shadow p-3 flex items-start justify-between gap-3">
                            <div class="text-sm font-medium">Payment successful 🎉 Your registration is confirmed.</div>
                            <button type="button" class="text-green-700 hover:text-green-900" @click="show = false" aria-label="Close">✕</button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="pointer-events-auto rounded-lg border border-red-200 bg-red-50 text-red-700 shadow p-3 flex items-start justify-between gap-3">
                            <div class="text-sm font-medium">{{ session('error') }}</div>
                            <button type="button" class="text-red-700 hover:text-red-900" @click="show = false" aria-label="Close">✕</button>
                        </div>
                    @endif

                    @if (request()->boolean('canceled'))
                        <div class="pointer-events-auto rounded-lg border border-amber-200 bg-amber-50 text-amber-800 shadow p-3 flex items-start justify-between gap-3">
                            <div class="text-sm font-medium">Checkout cancelled. You can try again.</div>
                            <button type="button" class="text-amber-800 hover:text-amber-900" @click="show = false" aria-label="Close">✕</button>
                        </div>
                    @endif
                </div>
            @endif


            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        <!-- Tom Select JS -->
        <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    </body>
</html>
