<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage ticket — {{ $event->name }}</h2>
    </x-slot>

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-rose-50 text-rose-700 border border-rose-200">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            @php
                $isFree = !$isPaid;
                $qty = max(1, (int)($registration->quantity ?? 1));
                $ad  = max(0, (int)($registration->party_adults ?? 0));
                $ch  = max(0, (int)($registration->party_children ?? 0));
                $extra = $ad + $ch;
                $party = 1 + $extra;
            @endphp

            <div class="mb-4 text-sm text-gray-600">
                Status: <span class="font-medium">{{ ucfirst($registration->status ?? 'pending') }}</span>
                @if($isPaid)
                    <span class="mx-2">·</span>Tickets: <span class="font-medium">{{ $qty }}</span>
                @else
                    <span class="mx-2">·</span>Party: <span class="font-medium">{{ $party }}</span>
                @endif
            </div>

            <form method="POST" action="{{ route('my.tickets.update', $registration->id) }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required
                               value="{{ old('email', $registration->email) }}"
                               class="mt-1 w-full rounded-lg border-gray-300" />
                        <p class="text-xs text-gray-500 mt-1">We’ll use this for your ticket and updates.</p>
                    </div>

                    @if($isFree)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sessions</label>
                            <div class="mt-2 space-y-2">
                                @forelse($event->sessions as $s)
                                    <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50">
                                        <input type="checkbox" class="h-4 w-4 rounded border-gray-300"
                                               name="session_ids[]"
                                               value="{{ $s->id }}"
                                               @checked(in_array($s->id, old('session_ids', $registration->sessions->pluck('id')->all())))>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $s->session_name }}</div>
                                            <div class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($s->session_date)->format('D, d M Y · g:ia') }}</div>
                                        </div>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-600">No sessions yet.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Adults</label>
                                <input type="number" name="party_adults" min="0" max="20"
                                       value="{{ old('party_adults', $ad) }}"
                                       class="mt-1 w-full rounded-lg border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Children</label>
                                <input type="number" name="party_children" min="0" max="20"
                                       value="{{ old('party_children', $ch) }}"
                                       class="mt-1 w-full rounded-lg border-gray-300" />
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-gray-600">
                            This is a paid event. You can update your email here.
                            Session changes and party size are locked after purchase.
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('my.tickets') }}" class="text-sm text-gray-600 hover:text-gray-800">Back</a>
                    <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
