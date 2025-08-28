<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Register for {{ $event->name }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-6 p-4 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Ticket</div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ ($event->ticket_cost ?? 0) > 0 ? '£'.number_format($event->ticket_cost,2) : 'Free' }}
                    </div>
                </div>
                @php
                    $image = $event->banner_url
                        ? asset('storage/' . $event->banner_url)
                        : ($event->avatar_url ? asset('storage/' . $event->avatar_url) : null);
                @endphp
                @if ($image)
                    <img src="{{ $image }}" alt="" class="h-12 w-12 rounded-lg object-cover">
                @endif
            </div>

            {{-- use model binding (public_id) --}}
            <form action="{{ route('events.register.store', $event) }}" method="POST" class="mt-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full name</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', optional(auth()->user())->name) }}"
                            class="mt-1 w-full rounded-lg border-gray-300"
                            required
                        >
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', optional(auth()->user())->email) }}"
                            class="mt-1 w-full rounded-lg border-gray-300"
                            required
                        >
                        @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Mobile (optional)</label>
                        <input type="text" name="mobile" value="{{ old('mobile') }}"
                               class="mt-1 w-full rounded-lg border-gray-300">
                        @error('mobile') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700">Select session(s)</label>
                    <div class="mt-2 space-y-2">
                        @forelse ($event->sessions as $s)
                            <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50">
                                <input
                                    type="checkbox"
                                    name="session_ids[]"
                                    value="{{ $s->id }}"
                                    class="h-4 w-4 rounded border-gray-300"
                                    @checked(in_array($s->id, old('session_ids', [])))
                                >
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $s->session_name }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($s->session_date)->format('D, d M Y · g:ia') }}
                                    </div>
                                </div>
                            </label>
                        @empty
                            <p class="text-sm text-gray-600">No sessions yet.</p>
                        @endforelse
                    </div>
                    @error('session_ids') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('events.show', $event) }}" class="text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                        {{ ($event->ticket_cost ?? 0) > 0 ? 'Proceed to Payment' : 'Register' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
