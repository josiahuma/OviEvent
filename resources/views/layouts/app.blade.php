<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ovievent') }}</title>

    @php
        $siteName   = config('app.name', 'ovievent');
        $defaultImg = asset('images/og-default.jpg');  /* keep a 1200x630 image here */
    @endphp

    {{-- Default Open Graph/Twitter; pages can override with @section('meta') --}}
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

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

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

    <main>
        {{ $slot }}
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
</body>
</html>
