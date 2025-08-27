<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ovievent') }}</title>

    @php
        $siteName   = config('app.name', 'ovievent');
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

    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>[x-cloak]{display:none!important}</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100">
    @include('layouts.navigation')

    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    {{-- GLOBAL FLASH TOASTS (Alpine + URL fallback) --}}
    <div id="flash-toasts"
         class="fixed left-4 right-4
                top-[calc(env(safe-area-inset-top)+64px)] sm:top-4
                z-[10000] space-y-2 pointer-events-none"
         role="status" aria-live="polite">

        {{-- Alpine version (renders only when the server has a message or url flag) --}}
        @if (session('success') || session('error') || request()->boolean('paid') || request()->boolean('canceled') || request()->boolean('registered'))
            <div x-data="{ show:true }" x-cloak x-show="show" x-init="setTimeout(()=>show=false,6000)" class="space-y-2">
                @if (session('success'))
                    <div class="pointer-events-auto rounded-lg border border-green-200 bg-green-50 text-green-800 shadow p-3 flex items-start justify-between gap-3">
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                        <button type="button" class="text-green-700 hover:text-green-900" @click="show=false" aria-label="Close">✕</button>
                    </div>
                @endif

                @if (request()->boolean('registered'))
                    <div class="pointer-events-auto rounded-lg border border-green-200 bg-green-50 text-green-800 shadow p-3 flex items-start justify-between gap-3">
                        <div class="text-sm font-medium">Registration confirmed 🎉 See you there!</div>
                        <button type="button" class="text-green-700 hover:text-green-900" @click="show=false" aria-label="Close">✕</button>
                    </div>
                @endif

                @if (request()->boolean('paid'))
                    <div class="pointer-events-auto rounded-lg border border-green-200 bg-green-50 text-green-800 shadow p-3 flex items-start justify-between gap-3">
                        <div class="text-sm font-medium">Payment successful 🎉 Your registration is confirmed.</div>
                        <button type="button" class="text-green-700 hover:text-green-900" @click="show=false" aria-label="Close">✕</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="pointer-events-auto rounded-lg border border-red-200 bg-red-50 text-red-700 shadow p-3 flex items-start justify-between gap-3">
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                        <button type="button" class="text-red-700 hover:text-red-900" @click="show=false" aria-label="Close">✕</button>
                    </div>
                @endif

                @if (request()->boolean('canceled'))
                    <div class="pointer-events-auto rounded-lg border border-amber-200 bg-amber-50 text-amber-800 shadow p-3 flex items-start justify-between gap-3">
                        <div class="text-sm font-medium">Checkout cancelled. You can try again.</div>
                        <button type="button" class="text-amber-800 hover:text-amber-900" @click="show=false" aria-label="Close">✕</button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <main>
        {{ $slot }}
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

{{-- AlpineJS fallback (only if your bundle didn’t start it) --}}
<script>
(function () {
    function startAlpine(){ if (window.Alpine && !window.Alpine.versionStarted){ try{ window.Alpine.start(); window.Alpine.versionStarted = true; }catch(e){} } }
    if (!window.Alpine) {
        var s = document.createElement('script');
        s.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
        s.defer = true;
        s.onload = startAlpine;
        document.head.appendChild(s);
    } else { startAlpine(); }
})();
</script>

{{-- Vanilla JS fallback toast (works even if Alpine & session fail) --}}
<script>
(function () {
    var p = new URLSearchParams(window.location.search);
    var msg = p.get('registered') ? 'Registration confirmed 🎉 See you there!'
            : p.get('paid')       ? 'Payment successful 🎉 Your registration is confirmed.'
            : p.get('canceled')   ? 'Checkout cancelled. You can try again.'
            : null;

    if (!msg) return;

    var host = document.getElementById('flash-toasts');
    if (!host) return;

    // If Alpine already rendered something, don’t duplicate
    if (host.querySelector('[data-fallback-toast]')) return;
    if (host.querySelector('[x-data]')) return;

    var box = document.createElement('div');
    box.setAttribute('data-fallback-toast', '1');
    box.className = 'pointer-events-auto rounded-lg border border-green-200 bg-green-50 text-green-800 shadow p-3 flex items-start justify-between gap-3';
    box.innerHTML = '<div class="text-sm font-medium"></div><button type="button" aria-label="Close">✕</button>';
    box.querySelector('div').textContent = msg;
    box.querySelector('button').className = 'text-green-700 hover:text-green-900';
    box.querySelector('button').onclick = function(){ host.remove(); };

    // little container so multiple messages would stack
    var wrap = document.createElement('div');
    wrap.className = 'space-y-2';
    wrap.appendChild(box);
    host.appendChild(wrap);

    setTimeout(function(){ if (host) host.remove(); }, 6000);
})();
</script>
</body>
</html>
