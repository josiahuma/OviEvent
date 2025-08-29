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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            @php
                // For paid events, count tickets (quantity, default 1)
                // For free events, count attendees (1 + adults + children)
                $totalUnits = $isPaidEvent
                    ? $event->registrations->sum(function ($r) {
                        return max(1, (int)($r->quantity ?? 1));
                    })
                    : $event->registrations->sum(function ($r) {
                        $ad = max(0, (int)($r->party_adults ?? 0));
                        $ch = max(0, (int)($r->party_children ?? 0));
                        return 1 + $ad + $ch; // registrant + guests
                    });
            @endphp

            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">
                    Total registrations
                    <span class="ml-1 text-xs text-gray-400">
                        ({{ $isPaidEvent ? 'tickets' : 'attendees' }})
                    </span>
                </div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">
                    {{ number_format($totalUnits) }}
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">Amount earned</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">
                    £{{ number_format($sumMinor/100, 2) }}
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div>
                    <div class="text-sm text-gray-500">Event type</div>
                    <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full
                        {{ $isPaidEvent ? 'bg-black/80 text-white' : 'bg-emerald-500 text-white' }}">
                        {{ $isPaidEvent ? 'Paid event' : 'Free event' }}
                    </div>
                </div>
            </div>
            @php
                $disablePayout = $payoutMinor <= 0 || !$isPaidEvent || $hasProcessingPayout;
                $payoutTitle   = $hasProcessingPayout
                    ? 'Payout request already processing'
                    : (!$isPaidEvent ? 'Free events have no payouts'
                    : ($payoutMinor <= 0 ? 'No payout available yet' : 'Request payout to your UK bank'));
            @endphp

            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Actions</div>
                        <div class="mt-1 text-xs text-gray-500">
                            Available payout:
                            <span class="font-medium text-gray-900">£{{ number_format($payoutMinor/100, 2) }}</span>
                        </div>
                    </div>

                    @if($hasProcessingPayout)
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-12.5a.75.75 0 00-1.5 0v4.19l-2.03 2.03a.75.75 0 101.06 1.06l2.22-2.22A.75.75 0 0010.75 10V5.5z" clip-rule="evenodd"/></svg>
                            Processing
                        </span>
                    @endif
                </div>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {{-- Email registrants --}}
                    <a href="{{ route('events.registrants.email', $event) }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.25 6.75A2.25 2.25 0 014.5 4.5h15a2.25 2.25 0 012.25 2.25v10.5A2.25 2.25 0 0119.5 19.5h-15A2.25 2.25 0 012.25 17.25V6.75zm2.72-.75l6.53 4.35a.75.75 0 00.8 0l6.53-4.35H4.97z"/>
                        </svg>
                        Email registrants
                    </a>

                    {{-- Request payout --}}
                    <form method="GET" action="{{ route('payouts.create', $event) }}" class="w-full">
                        <input type="hidden" name="amount" value="{{ $payoutMinor }}">
                        <button type="submit" title="{{ $payoutTitle }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium
                                {{ $disablePayout
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500' }}"
                            {{ $disablePayout ? 'disabled aria-disabled=true' : '' }}>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 7.5A2.25 2.25 0 015.25 5.25h13.5A2.25 2.25 0 0121 7.5v9a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 16.5v-9zM6 8.25a.75.75 0 000 1.5h12a.75.75 0 000-1.5H6zm0 4a.75.75 0 000 1.5h7.5a.75.75 0 000-1.5H6z"/>
                            </svg>
                            Request payout
                        </button>
                    </form>
                </div>
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
                    @php
                        $qty = max(1, (int)($reg->quantity ?? 1));

                        $adults   = max(0, (int)($reg->party_adults ?? 0));
                        $children = max(0, (int)($reg->party_children ?? 0));
                        $extra    = $adults + $children;      // additional guests (not counting registrant)
                        $party    = 1 + $extra;               // total party size including registrant
                    @endphp

                    <div class="p-4 flex items-start justify-between gap-4">
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ $reg->name ?? 'Unnamed' }}
                                <span class="text-gray-500 font-normal">· {{ $reg->email ?? 'no email' }}</span>
                            </div>

                            {{-- Sessions --}}
                            @if ($reg->sessions && $reg->sessions->count())
                                <div class="mt-1 text-sm text-gray-600">
                                    Sessions:
                                    <span class="font-medium">
                                        {{ $reg->sessions->pluck('session_name')->join(', ') }}
                                    </span>
                                </div>
                            @endif

                            {{-- Party / Tickets --}}
                            @if ($isPaidEvent)
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 text-xs font-medium">
                                        Tickets: {{ $qty }}
                                    </span>
                                </div>
                            @else
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-medium">
                                        Guests: {{ $extra }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-800 text-xs font-medium">
                                        Party: {{ $party }}
                                    </span>
                                    @if($extra > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-medium">
                                            {{ $adults }} adult{{ $adults === 1 ? '' : 's' }}, {{ $children }} child{{ $children === 1 ? '' : 'ren' }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="text-right">
                            <div class="text-sm text-gray-500">{{ optional($reg->created_at)->format('d M Y, g:ia') }}</div>

                            {{-- keep old paid badge if you still set $reg->is_paid somewhere --}}
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
