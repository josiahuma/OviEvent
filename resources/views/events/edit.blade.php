<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Event — {{ $event->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800 underline">Back to dashboard</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto p-6 bg-white rounded-2xl border shadow-sm">
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 border border-red-200 p-3 rounded mb-4">
                <ul class="list-disc ml-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            // normalize tags to array
            $raw = $event->tags;
            if (is_array($raw))       { $tags = $raw; }
            elseif (is_string($raw))  {
                $decoded = json_decode($raw, true);
                $tags = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                    ? $decoded
                    : array_filter(array_map('trim', preg_split('/[,;]+/', $raw)));
            } else { $tags = []; }

            $cats = [
                'Arts','Business','Charity','Community','Education','Entertainment','Food & Drink',
                'Fashion','Health','Music','Religion','Sports','Technology','Travel'
            ];
        @endphp

        <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5">
                {{-- Name --}}
                <div>
                    <label class="block text-gray-700 font-semibold">Event Name</label>
                    <input type="text" name="name" value="{{ old('name', $event->name) }}" class="w-full border rounded p-2" required>
                </div>

                {{-- Organizer + Category --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold">Organizer</label>
                        <input type="text" name="organizer" value="{{ old('organizer', $event->organizer) }}" class="w-full border rounded p-2" placeholder="Organizer Name">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold">Category</label>
                        <select name="category" class="w-full border rounded p-2">
                            <option value="">-- Select Category --</option>
                            @foreach ($cats as $cat)
                                <option value="{{ $cat }}" @selected(old('category', $event->category) === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="block text-gray-700 font-semibold">Tags</label>
                    <select id="tags" name="tags[]" multiple class="w-full border rounded p-2">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag }}" selected>{{ $tag }}</option>
                        @endforeach
                    </select>
                    <small class="text-gray-500">Type and press enter to add a tag.</small>
                </div>

                {{-- Location (Google Places) --}}
                <div>
                    <label class="block text-gray-700 font-semibold">Location</label>
                    <input
                        id="location-input"
                        type="text"
                        name="location"
                        class="w-full border rounded p-2"
                        placeholder="Venue, address or place name"
                        value="{{ old('location', $event->location) }}"
                        autocomplete="off"
                    >
                    <input type="hidden" name="location_place_id" id="location_place_id" value="{{ old('location_place_id', $event->location_place_id ?? '') }}">
                    <input type="hidden" name="location_lat"      id="location_lat"      value="{{ old('location_lat', $event->location_lat ?? '') }}">
                    <input type="hidden" name="location_lng"      id="location_lng"      value="{{ old('location_lng', $event->location_lng ?? '') }}">
                    <small class="text-gray-500">Start typing and choose a suggestion.</small>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-gray-700 font-semibold">Description</label>
                    <textarea name="description" rows="4" class="w-full border rounded p-2">{{ old('description', $event->description) }}</textarea>
                </div>

                {{-- Ticket & Images --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold">Ticket Cost (£)</label>
                        <input type="number" name="ticket_cost" step="0.01" value="{{ old('ticket_cost', $event->ticket_cost) }}" class="w-full border rounded p-2">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold">Event Avatar (replace)</label>
                        <input type="file" name="avatar" class="w-full border rounded p-2">
                        @if ($event->avatar_url)
                            <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                <img src="{{ asset('storage/'.$event->avatar_url) }}" alt="avatar" class="h-10 w-10 rounded object-cover">
                                <span class="underline truncate">{{ $event->avatar_url }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold">Event Banner (replace)</label>
                    <input type="file" name="banner" class="w-full border rounded p-2">
                    @if ($event->banner_url)
                        <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                            <img src="{{ asset('storage/banners'.$event->banner_url) }}" alt="banner" class="h-12 w-20 rounded object-cover">
                            <span class="underline truncate">{{ $event->banner_url }}</span>
                        </div>
                    @endif
                </div>

                {{-- Sessions --}}
                <div class="mb-2">
                    <label class="block text-gray-800 font-semibold">Event sessions</label>
                    <p class="text-sm text-gray-500">
                        Add each date/time attendees can choose. You can add as many sessions as you need.
                    </p>
                </div>

                <div id="sessions-wrapper" class="space-y-4">
                    @php
                        $existing = $event->sessions()->orderBy('session_date')->get();
                    @endphp

                    @forelse ($existing as $i => $s)
                        @php
                            $date = \Carbon\Carbon::parse($s->session_date);
                        @endphp
                        <div class="session-item rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-medium text-gray-700">Session {{ $i+1 }}</h4>
                                <button type="button" class="remove-session text-sm text-rose-600 hover:text-rose-700">
                                    Remove
                                </button>
                            </div>

                            <input type="hidden" name="sessions[{{ $i }}][id]" value="{{ $s->id }}">
                            <input type="hidden" name="sessions[{{ $i }}][_delete]" value="0">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Title</label>
                                    <input type="text"
                                           name="sessions[{{ $i }}][name]"
                                           value="{{ old("sessions.$i.name", $s->session_name) }}"
                                           placeholder="e.g., Sunday Morning Service"
                                           class="w-full rounded-lg border-gray-300"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">What you call this session.</p>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Date</label>
                                    <input type="date"
                                           name="sessions[{{ $i }}][date]"
                                           value="{{ old("sessions.$i.date", $date->format('Y-m-d')) }}"
                                           class="w-full rounded-lg border-gray-300"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">The calendar date.</p>
                                </div>

                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Start time</label>
                                    <input type="time"
                                           name="sessions[{{ $i }}][time]"
                                           value="{{ old("sessions.$i.time", $date->format('H:i')) }}"
                                           class="w-full rounded-lg border-gray-300"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">Local start time.</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- if no sessions yet, show one blank row (index 0) --}}
                        <div class="session-item rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-medium text-gray-700">Session 1</h4>
                                <button type="button" class="remove-session text-sm text-rose-600 hover:text-rose-700 hidden">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Title</label>
                                    <input type="text" name="sessions[0][name]" class="w-full rounded-lg border-gray-300" placeholder="e.g., Sunday Morning Service" required>
                                    <p class="mt-1 text-xs text-gray-500">What you call this session.</p>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Date</label>
                                    <input type="date" name="sessions[0][date]" class="w-full rounded-lg border-gray-300" required>
                                    <p class="mt-1 text-xs text-gray-500">The calendar date.</p>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Start time</label>
                                    <input type="time" name="sessions[0][time]" class="w-full rounded-lg border-gray-300" required>
                                    <p class="mt-1 text-xs text-gray-500">Local start time.</p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <button type="button" id="add-session"
                        class="mt-1 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                    + Add another session
                </button>

                {{-- Submit --}}
                <div class="pt-4">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Save Changes
                    </button>
                    <a href="{{ route('dashboard') }}" class="ml-3 text-gray-600 hover:text-gray-800 underline">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tom Select --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        // Tags
        document.addEventListener("DOMContentLoaded", function () {
            new TomSelect("#tags", {
                plugins: ['remove_button'],
                persist: false,
                create: true,
                createOnBlur: true,
                maxItems: null,
                placeholder: "Add tags…",
                delimiter: ',',
            });
        });

        // Sessions UI (supports mark-for-delete for existing rows)
        (function () {
            let sessionIndex = {{ max( ($existing->count() ?? 0), 1 ) }}; // next index

            const wrapper = document.getElementById('sessions-wrapper');
            const addBtn   = document.getElementById('add-session');

            function renumber() {
                const items = wrapper.querySelectorAll('.session-item');
                items.forEach((el, i) => {
                    el.querySelector('h4').textContent = `Session ${i + 1}`;
                    const btn = el.querySelector('.remove-session');
                    btn.classList.toggle('hidden', items.length === 1);
                });
            }

            addBtn.addEventListener('click', () => {
                const html = `
                <div class="session-item rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-700">Session</h4>
                        <button type="button" class="remove-session text-sm text-rose-600 hover:text-rose-700">Remove</button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Title</label>
                            <input type="text" name="sessions[${sessionIndex}][name]" placeholder="e.g., Sunday Morning Service" class="w-full rounded-lg border-gray-300" required>
                            <p class="mt-1 text-xs text-gray-500">What you call this session.</p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Date</label>
                            <input type="date" name="sessions[${sessionIndex}][date]" class="w-full rounded-lg border-gray-300" required>
                            <p class="mt-1 text-xs text-gray-500">The calendar date.</p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Start time</label>
                            <input type="time" name="sessions[${sessionIndex}][time]" class="w-full rounded-lg border-gray-300" required>
                            <p class="mt-1 text-xs text-gray-500">Local start time.</p>
                        </div>
                    </div>
                </div>`;
                wrapper.insertAdjacentHTML('beforeend', html);
                sessionIndex++;
                renumber();
            });

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.remove-session');
                if (!btn) return;
                const item = btn.closest('.session-item');
                // if this row has an existing id, mark for deletion; else remove outright
                const del = item.querySelector('input[name$="[_delete]"]');
                if (del) { del.value = '1'; item.style.display = 'none'; } else { item.remove(); }
                renumber();
            });

            renumber();
        })();

        // Google Places Autocomplete
        window.initPlaces = function () {
            const input = document.getElementById('location-input');
            if (!input || !window.google || !google.maps || !google.maps.places) return;

            const ac = new google.maps.places.Autocomplete(input, {
                fields: ['place_id','geometry','formatted_address','name'],
                types: ['geocode']
            });

            ac.addListener('place_changed', () => {
                const place = ac.getPlace();
                if (!place || !place.geometry) return;

                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                document.getElementById('location_place_id').value = place.place_id || '';
                document.getElementById('location_lat').value = lat;
                document.getElementById('location_lng').value = lng;

                if (place.formatted_address) input.value = place.formatted_address;
            });
        };
    </script>

    @if (config('services.google.maps_key'))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google.maps_key')) }}&libraries=places&callback=initPlaces" async defer></script>
    @endif
</x-app-layout>
