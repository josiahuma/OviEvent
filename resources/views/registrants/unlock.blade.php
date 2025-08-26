<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Unlock registrants — {{ $event->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800 underline">Back to dashboard</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900">One-time unlock</h3>
            <p class="mt-1 text-gray-600">
                This event is free. To view detailed registrant information, purchase a one-time unlock for this event.
                You’ll be able to see attendee names, emails, and selected sessions.
            </p>

            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Event: <span class="font-medium text-gray-900">{{ $event->name }}</span>
                </div>
                <div class="text-2xl font-semibold text-gray-900">
                    {{ $currency === 'GBP' ? '£' : '' }}{{ number_format($amount/100, 2) }}
                    <span class="text-sm text-gray-500">{{ $currency }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('events.registrants.checkout', $event) }}" class="mt-6">
                @csrf
                <button type="submit"
                    class="inline-flex items-center justify-center w-full px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                    Pay & unlock with Stripe
                </button>
            </form>

            <p class="mt-3 text-xs text-gray-500">
                Secure checkout via Stripe. You’ll be redirected back once payment completes.
            </p>
        </div>
    </div>
</x-app-layout>
