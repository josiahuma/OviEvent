{{-- resources/views/static/pricing.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Pricing
        </h2>
    </x-slot>

    {{-- Hero – consistent with How it works --}}
    <div class="bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-12 text-white">
                <h1 class="text-3xl sm:text-4xl font-bold">Simple, fair pricing</h1>
                <p class="mt-3 text-indigo-100 max-w-3xl">
                    Post unlimited events for free. If you sell tickets, checkout runs on Stripe and we take a small commission per paid ticket.
                    No monthly fees. No setup fees.
                </p>
                <div class="mt-6">
                    <a href="{{ auth()->check() ? route('events.create') : route('register') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-indigo-700 font-semibold shadow hover:shadow-md">
                        Get started — it’s free
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 3.75a.75.75 0 011.5 0v10.19l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V3.75z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

        {{-- Tiers --}}
        <div class="grid md:grid-cols-2 gap-6">
            {{-- FREE TIER (badge ABOVE card) --}}
            <div class="space-y-2">
                <div class="flex justify-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 shadow-sm">
                        Most popular for free meetups
                    </span>
                </div>

                <div class="overflow-hidden rounded-2xl bg-white/80 backdrop-blur ring-1 ring-gray-200 shadow-sm hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-gray-900">Free events</h3>
                            <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full text-xs font-medium">
                                £0 <span class="text-gray-500">/ forever</span>
                            </span>
                        </div>

                        <p class="mt-2 text-gray-600">
                            Create and publish unlimited events at no cost. Great for free community events, workshops, and meetups.
                        </p>

                        <ul class="mt-5 space-y-2 text-gray-700">
                            @php
                                $freeFeatures = [
                                    'Unlimited events & sessions',
                                    'Beautiful event pages (banner, avatar, tags, categories)',
                                    'Registration & attendee management',
                                    'No fees on free tickets',
                                ];
                            @endphp
                            @foreach ($freeFeatures as $f)
                                <li class="flex items-start gap-3">
                                    <svg class="mt-0.5 h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.07 7.071a1 1 0 01-1.415 0L3.293 9.85a1 1 0 111.414-1.415l3.1 3.1 6.364-6.364a1 1 0 011.536.121z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6">
                            <a href="{{ auth()->check() ? route('events.create') : route('register') }}"
                               class="inline-flex w-full items-center justify-center px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold shadow hover:bg-indigo-700">
                                Get started — free
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PAID TIER (badge ABOVE card) --}}
            <div class="space-y-2">
                <div class="flex justify-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100 shadow-sm">
                        Best for ticketed events
                    </span>
                </div>

                <div class="overflow-hidden rounded-2xl bg-white/80 backdrop-blur ring-2 ring-indigo-300 shadow-md hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-gray-900">Paid events</h3>
                            <span class="inline-flex items-center gap-1 text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-full text-xs font-medium">
                                Commission per paid ticket
                            </span>
                        </div>

                        <p class="mt-2 text-gray-600">
                            Charge for tickets with secure Stripe checkout. Payouts go directly to you. We only deduct a small commission.
                        </p>

                        <ul class="mt-5 space-y-2 text-gray-700">
                            @php
                                $paidFeatures = [
                                    'Unlimited events & sessions',
                                    'Stripe payments (secure checkout)',
                                    'Payouts direct to your Stripe account',
                                    'No monthly or setup fees — commission only on paid tickets',
                                    'Attendee/session management dashboard',
                                ];
                            @endphp
                            @foreach ($paidFeatures as $f)
                                <li class="flex items-start gap-3">
                                    <svg class="mt-0.5 h-5 w-5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.07 7.071a1 1 0 01-1.415 0L3.293 9.85a1 1 0 111.414-1.415l3.1 3.1 6.364-6.364a1 1 0 011.536.121z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="mt-4 text-sm text-gray-500">
                            Exact fees are shown at checkout and deducted automatically per paid ticket.
                        </p>

                        <div class="mt-6">
                            <a href="{{ auth()->check() ? route('events.create') : route('register') }}"
                               class="inline-flex w-full items-center justify-center px-4 py-2 rounded-xl bg-white text-indigo-700 font-semibold shadow ring-1 ring-indigo-200 hover:bg-indigo-50">
                                Get started with paid tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary highlights --}}
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900">Event creation</h4>
                <p class="mt-2 text-gray-600">Unlimited events, sessions, banners & avatars — designed for sharing.</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900">Registration</h4>
                <p class="mt-2 text-gray-600">Attendees can pick specific sessions. You track everything in your dashboard.</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900">Payments</h4>
                <p class="mt-2 text-gray-600">Stripe checkout for paid tickets. Commission only — no ongoing fees.</p>
            </div>
        </div>

        {{-- Final CTA --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex items-center justify-between flex-col sm:flex-row gap-4">
            <div>
                <h4 class="font-semibold text-gray-900">Ready to host?</h4>
                <p class="text-gray-600">Start free. Upgrade to paid tickets whenever you’re ready.</p>
            </div>
            <a href="{{ auth()->check() ? route('events.create') : route('register') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold shadow hover:bg-indigo-700">
                Create event
            </a>
        </div>
    </div>
</x-app-layout>
