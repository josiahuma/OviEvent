<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage booking — {{ $event->name }}
        </h2>
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
            <form method="POST" action="{{ route('events.ticket.update', ['event' => $event, 'reg' => $registration->id, 'signature' => request('signature'), 'expires' => request('expires')]) }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required
                               value="{{ old('email', $registration->email) }}"
                               class="mt-1 w-full rounded-lg border-gray-300" />
                    </div>

                    @if($isFreeEvent)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Adults</label>
                                <input type="number" name="party_adults" min="0" max="20"
                                       value="{{ old('party_adults', (int)($registration->party_adults ?? 0)) }}"
                                       class="mt-1 w-full rounded-lg border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Children</label>
                                <input type="number" name="party_children" min="0" max="20"
                                       value="{{ old('party_children', (int)($registration->party_children ?? 0)) }}"
                                       class="mt-1 w-full rounded-lg border-gray-300" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Adding additional attendee applies only to free events.</p>
                    @else
                        <p class="text-sm text-gray-600">This is a paid event. You can only update your email here. (To increase ticket quantity you'd have to purchase another ticket.)</p>
                    @endif
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('events.show', $event) }}" class="text-sm text-gray-600 hover:text-gray-800">Back to event</a>
                    <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
