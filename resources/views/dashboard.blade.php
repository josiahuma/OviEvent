<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            <a href="{{ route('events.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg>
                Create Event
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if ($events->count())
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($events as $event)
                    @php
                        $image = $event->banner_url
                            ? asset('storage/' . $event->banner_url)
                            : ($event->avatar_url ? asset('storage/' . $event->avatar_url) : null);

                        $nextDate = $event->sessions_min_session_date ?? null;

                        // robust tags parsing (optional to show)
                        $raw = $event->tags;
                        $tags = [];
                        if (is_array($raw)) {
                            $tags = $raw;
                        } elseif (is_string($raw) && $raw !== '') {
                            $decoded = json_decode($raw, true);
                            $tags = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                                ? $decoded
                                : array_filter(array_map('trim', preg_split('/[,;]+/', $raw)));
                        }
                    @endphp

                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm ring-1 ring-gray-200">
                        <div class="relative">
                            @if ($image)
                                <img src="{{ $image }}" alt="{{ $event->name }}" class="h-40 w-full object-cover" loading="lazy" decoding="async">
                            @else
                                <div class="h-40 w-full bg-gradient-to-br from-slate-200 to-slate-100 flex items-center justify-center">
                                    <span class="text-slate-500 text-sm">No image</span>
                                </div>
                            @endif

                            @if ($event->is_promoted ?? false)
                                <span class="absolute top-3 left-3 bg-amber-500/95 text-white text-xs font-semibold px-2.5 py-1 rounded-md shadow">
                                    Promoted
                                </span>
                            @endif

                            <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ ($event->ticket_cost ?? 0) == 0 ? 'bg-emerald-500 text-white' : 'bg-black/80 text-white' }}">
                                {{ ($event->ticket_cost ?? 0) == 0 ? 'Free' : '£' . number_format($event->ticket_cost, 2) }}
                            </span>
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ $event->name }}</h3>
                                    @if($event->category)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $event->category }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">Registrations</div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $event->registrations_count ?? 0 }}</div>
                                </div>
                            </div>

                            @if ($event->location)
                                <div class="mt-2 flex items-center text-sm text-gray-600 gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C8.686 2 6 4.686 6 8c0 4.418 6 12 6 12s6-7.582 6-12c0-3.314-2.686-6-6-6zm0 8.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
                                    </svg>
                                    <span class="line-clamp-1">{{ $event->location }}</span>
                                </div>
                            @endif

                            <div class="mt-1 flex items-center text-sm text-gray-600 gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1z"/>
                                    <path d="M3 10h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8z"/>
                                </svg>
                                @if ($nextDate)
                                    <span>Next: {{ \Carbon\Carbon::parse($nextDate)->format('D, d M Y · g:ia') }}</span>
                                @else
                                    <span>No sessions yet</span>
                                @endif
                            </div>

                            @if (!empty($tags))
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($tags as $tag)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-50 text-blue-700 border border-blue-100">#{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-4 flex items-center justify-between">
                                 <div class="flex items-center gap-2">
                                    <a href="{{ route('events.show', $event->id) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-800">
                                        View
                                    </a>
                                    <a href="{{ route('events.edit', $event) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">
                                        Edit
                                    </a>
                                </div>

                                {{-- View registrants / Unlock --}}
                                @php
                                    $isFree = ($event->ticket_cost ?? 0) == 0;
                                    $isUnlocked = optional($event->unlocks->first())->unlocked_at !== null;
                                @endphp

                                @if(!$isFree || $isUnlocked)
                                    {{-- Paid event or unlocked free event --}}
                                    <a href="{{ route('events.registrants', $event) }}"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-emerald-600 text-white hover:bg-emerald-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a5 5 0 00-5 5v2H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 00-2-2h-1V7a5 5 0 00-5-5zm-3 7V7a3 3 0 016 0v2H9z"/></svg>
                                        View registrants
                                    </a>
                                @else
                                    {{-- Free event & not unlocked: show "locked" variant that goes to unlock page --}}
                                    <a href="{{ route('events.registrants.unlock', $event) }}"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a4 4 0 00-4 4v2H7a2 2 0 00-2 2v9a2 2 0 002 2h10a2 2 0 002-2v-9a2 2 0 00-2-2h-1V6a4 4 0 00-8 0v2h2V6a2 2 0 114 0v2h-4z"/></svg>
                                        View registrants
                                    </a>
                                @endif
                                
                                {{-- Delete button with confirmation --}}
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = true"
                                            class="inline-flex items-center px-3 py-1.5 text-sm rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700">
                                        Delete
                                    </button>

                                    {{-- Confirm dropdown --}}
                                    <div x-show="open" @click.outside="open=false" x-cloak
                                         class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg p-3 z-10">
                                        <p class="text-sm text-gray-700">Delete this event? This action cannot be undone.</p>
                                        <div class="mt-3 flex items-center justify-end gap-2">
                                            <button @click="open=false" class="text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                                            <form method="POST" action="{{ route('events.destroy', $event) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 text-sm rounded-md bg-rose-600 hover:bg-rose-700 text-white">
                                                    Yes, delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $events->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                <h3 class="text-lg font-semibold text-gray-800">You haven’t created any events yet</h3>
                <p class="mt-1 text-sm text-gray-500">Create your first event to get started.</p>
                <a href="{{ route('events.create') }}"
                   class="mt-4 inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    + Create Event
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
