<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registrants — {{ $event->name }}
            </h2>
            <div class="flex items-center gap-4">
                <a href="{{ route('payouts.index') }}" class="text-sm text-gray-600 hover:text-gray-800 underline">Payouts</a>
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800 underline">Back to dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Top KPI tiles --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">Total registrations</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $event->registrations->count() }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">Amount earned</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">
                    £{{ number_format($sumMinor/100, 2) }}
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">Payout (after 20%)</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">
                    £{{ number_format($payoutMinor/100, 2) }}
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Event type</div>
                    <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full
                        {{ $isPaidEvent ? 'bg-black/80 text-white' : 'bg-emerald-500 text-white' }}">
                        {{ $isPaidEvent ? 'Paid event' : 'Free event' }}
                    </div>
                </div>

                @php
                    $disableBtn = $payoutMinor <= 0 || !$isPaidEvent || $hasProcessingPayout;
                @endphp

                <form method="GET" action="{{ route('payouts.create', $event) }}">
                    <input type="hidden" name="amount" value="{{ $payoutMinor }}">
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium
                               {{ $disableBtn ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700' }}"
                        {{ $disableBtn ? 'disabled' : '' }}>
                        Request payout
                    </button>
                </form>
            </div>
        </div>

        {{-- Registrants list --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ $event->registrations->count() }} registrant{{ $event->registrations->count() === 1 ? '' : 's' }}
                </div>
            </div>

            <div class="divide-y">
                @forelse ($event->registrations as $reg)
                    <div class="p-4 flex items-start justify-between gap-4">
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ $reg->name ?? 'Unnamed' }}
                                <span class="text-gray-500 font-normal">· {{ $reg->email ?? 'no email' }}</span>
                            </div>
                            @if ($reg->sessions && $reg->sessions->count())
                                <div class="mt-1 text-sm text-gray-600">
                                    Sessions:
                                    <span class="font-medium">
                                        {{ $reg->sessions->pluck('session_name')->join(', ') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">{{ optional($reg->created_at)->format('d M Y, g:ia') }}</div>
                            @if(isset($reg->is_paid))
                                <div class="mt-1 text-xs inline-flex items-center px-2 py-0.5 rounded-full
                                    {{ $reg->is_paid ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $reg->is_paid ? 'Paid' : 'Pending' }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-600">No registrations yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
