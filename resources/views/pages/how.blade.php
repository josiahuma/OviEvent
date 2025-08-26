{{-- resources/views/static/how-it-works.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            How it works
        </h2>
    </x-slot>

    <div class="bg-gradient-to-r from-indigo-600 to-violet-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-12 text-white">
                <h1 class="text-3xl sm:text-4xl font-bold">Create. Share. Sell. Smooth.</h1>
                <p class="mt-3 text-indigo-100 max-w-3xl">
                    Publish beautiful event pages, add sessions, and accept registrations in minutes.
                    Free events are free. Paid events use Stripe with a small commission per ticket.
                </p>
                <div class="mt-6">
                    <a href="{{ auth()->check() ? route('events.create') : route('register') }}"
                       class="inline-flex items-center px-4 py-2 rounded-xl bg-white text-indigo-700 font-semibold shadow hover:shadow-md">
                        Create your first event
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900">1) Create event</h3>
                <p class="mt-2 text-gray-600">Add name, organizer, images, tags and ticket cost.</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900">2) Add sessions</h3>
                <p class="mt-2 text-gray-600">Attach one or many dates/times so attendees pick what suits them.</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900">3) Share &amp; get paid</h3>
                <p class="mt-2 text-gray-600">Share your link. For paid events, payments go via Stripe; we take a small commission per ticket.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900">Beautiful event pages</h4>
                <p class="mt-2 text-gray-600">Event avatar + banner, rich descriptions, tags and categories — optimized for sharing.</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900">Sessions &amp; registrations</h4>
                <p class="mt-2 text-gray-600">Attendees can select specific sessions; you’ll see everything in your dashboard.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900">Stripe payments</h4>
                <p class="mt-2 text-gray-600">Secure checkout. Payouts go to you. We only take a commission on paid tickets.</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h4 class="font-semibold text-gray-900">Unlimited events</h4>
                <p class="mt-2 text-gray-600">No monthly fees. Create as many events as you like.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex items-center justify-between">
            <div>
                <h4 class="font-semibold text-gray-900">Ready to publish?</h4>
                <p class="text-gray-600">It only takes a minute to create your first event.</p>
            </div>
            <a href="{{ auth()->check() ? route('events.create') : route('register') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold shadow hover:bg-indigo-700">
                Create event
            </a>
        </div>
    </div>
</x-app-layout>
