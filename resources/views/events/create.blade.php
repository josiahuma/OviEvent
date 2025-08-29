<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Event
        </h2>
    </x-slot>

    <div class="container mx-auto max-w-2xl p-6 bg-white rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Create New Event</h2>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Event Name -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Event Name</label>
                <input type="text" name="name" class="w-full border rounded p-2" required>
            </div>

            <!-- Organizer -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Organizer</label>
                <input type="text" name="organizer" class="w-full border rounded p-2" placeholder="Organizer Name">
            </div>

            <!-- Category -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Category</label>
                <select name="category" class="w-full border rounded p-2">
                    <option value="">-- Select Category --</option>
                    <option value="Arts">Arts</option>
                    <option value="Business">Business</option>
                    <option value="Charity">Charity</option>
                    <option value="Community">Community</option>
                    <option value="Education">Education</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Food & Drink">Food & Drink</option>
                    <option value="Fashion">Fashion</option>
                    <option value="Health">Health</option>
                    <option value="Music">Music</option>
                    <option value="Religion">Religion</option>
                    <option value="Sports">Sports</option>
                    <option value="Technology">Technology</option>
                    <option value="Travel">Travel</option>
                </select>
            </div>

            <!-- Tags -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Tags</label>
                <select id="tags" name="tags[]" multiple class="w-full border rounded p-2"></select>
                <small class="text-gray-500">Type and press enter to add a tag. You can add multiple tags.</small>
            </div>

            <!-- Location (Google Places Autocomplete) -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Location</label>

                <input
                    id="location-input"
                    type="text"
                    name="location"
                    class="w-full border rounded p-2"
                    placeholder="Venue, address or place name"
                    autocomplete="off"
                >

                <!-- Hidden fields to capture extra details (optional but useful) -->
                <input type="hidden" name="location_place_id" id="location_place_id">
                <input type="hidden" name="location_lat" id="location_lat">
                <input type="hidden" name="location_lng" id="location_lng">

                <small class="text-gray-500">Start typing and choose a suggestion.</small>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Description</label>
                <textarea name="description" class="w-full border rounded p-2" rows="4"></textarea>
            </div>

            <!-- Ticket Cost + Currency -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-semibold">Ticket Cost</label>
                <input type="number" step="0.01" name="ticket_cost" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold">Currency</label>
                <select name="ticket_currency" class="w-full border rounded p-2">
                @foreach (['GBP','USD','EUR','NGN','KES','GHS','ZAR','CAD','AUD'] as $cur)
                    <option value="{{ $cur }}" @selected(old('ticket_currency', $event->ticket_currency ?? 'GBP') === $cur)>{{ $cur }}</option>
                @endforeach
                </select>
            </div>
            </div>

            <!-- Event Banner -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Event Banner (Required *) </label>
                <p class="text-sm text-gray-500">
                This image appears at the top of your event page. Recommended size: 1200x300 pixels (4:1 ratio).
                </p>
                <input type="file" name="banner" class="w-full border rounded p-2">
            </div>

            <!-- Event Avatar -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Event Avatar (Optional) </label>
                <p class="text-sm text-gray-500">
                    This image is used by attendees to create personalised display picture of 'I will be attending'
                </p>
                <input type="file" name="avatar" class="w-full border rounded p-2">
            </div>

            <!-- Event Sessions -->
            <div class="mb-6">
            <div class="mb-2">
                <label class="block text-gray-800 font-semibold">Event sessions</label>
                <p class="text-sm text-gray-500">
                Add each date/time attendees can choose. You can add as many sessions as you need.
                </p>
            </div>

            <div id="sessions-wrapper" class="space-y-4">
                {{-- Session 1 (starter row) --}}
                <div class="session-item rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-700">Session 1</h4>
                    <button type="button"
                            class="remove-session text-sm text-rose-600 hover:text-rose-700 hidden">
                    Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                    <label class="block text-sm text-gray-700 mb-1">Title</label>
                    <input type="text"
                            name="sessions[0][name]"
                            placeholder="e.g., Sunday Morning Service"
                            class="w-full rounded-lg border-gray-300"
                            required>
                    <p class="mt-1 text-xs text-gray-500">What you call this session.</p>
                    </div>

                    <div>
                    <label class="block text-sm text-gray-700 mb-1">Date</label>
                    <input type="date"
                            name="sessions[0][date]"
                            class="w-full rounded-lg border-gray-300"
                            required>
                    <p class="mt-1 text-xs text-gray-500">The calendar date.</p>
                    </div>

                    <div>
                    <label class="block text-sm text-gray-700 mb-1">Start time</label>
                    <input type="time"
                            name="sessions[0][time]"
                            class="w-full rounded-lg border-gray-300"
                            required>
                    <p class="mt-1 text-xs text-gray-500">Local start time.</p>
                    </div>
                </div>
                </div>
            </div>

            <button type="button" id="add-session"
                    class="mt-3 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                + Add another session
            </button>
            </div>


            <!-- Submit -->
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Create Event
            </button>
        </form>
    </div>

    <script>
        // Sessions UI
        (function () {
            let sessionIndex = 1; // next numeric index for name="sessions[index][...]"

            const wrapper = document.getElementById('sessions-wrapper');
            const addBtn   = document.getElementById('add-session');

            function renumber() {
            const items = wrapper.querySelectorAll('.session-item');
            items.forEach((el, i) => {
                el.querySelector('h4').textContent = `Session ${i + 1}`;
                const removeBtn = el.querySelector('.remove-session');
                removeBtn.classList.toggle('hidden', items.length === 1);
            });
            }

            addBtn.addEventListener('click', () => {
            const html = `
                <div class="session-item rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-700">Session</h4>
                    <button type="button" class="remove-session text-sm text-rose-600 hover:text-rose-700">
                    Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                    <label class="block text-sm text-gray-700 mb-1">Title</label>
                    <input type="text"
                            name="sessions[${sessionIndex}][name]"
                            placeholder="e.g., Sunday Morning Service"
                            class="w-full rounded-lg border-gray-300"
                            required>
                    <p class="mt-1 text-xs text-gray-500">What you call this session.</p>
                    </div>

                    <div>
                    <label class="block text-sm text-gray-700 mb-1">Date</label>
                    <input type="date"
                            name="sessions[${sessionIndex}][date]"
                            class="w-full rounded-lg border-gray-300"
                            required>
                    <p class="mt-1 text-xs text-gray-500">The calendar date.</p>
                    </div>

                    <div>
                    <label class="block text-sm text-gray-700 mb-1">Start time</label>
                    <input type="time"
                            name="sessions[${sessionIndex}][time]"
                            class="w-full rounded-lg border-gray-300"
                            required>
                    <p class="mt-1 text-xs text-gray-500">Local start time.</p>
                    </div>
                </div>
                </div>
            `;
            wrapper.insertAdjacentHTML('beforeend', html);
            sessionIndex++;
            renumber();
            });

            document.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-session');
            if (!btn) return;
            const item = btn.closest('.session-item');
            if (item) item.remove();
            renumber();
            });

            // Initialize remove button visibility and headings
            renumber();
        })();

        // Tags (TomSelect)
        document.addEventListener("DOMContentLoaded", function () {
            if (window.TomSelect) {
                new TomSelect("#tags", {
                    plugins: ['remove_button'],
                    persist: false,
                    create: true,
                    createOnBlur: true,
                    maxItems: null,
                    placeholder: "Add tags...",
                    delimiter: ','
                });
            }
        });

        // --- Google Places Autocomplete ---
        window.initPlaces = function () {
            const input = document.getElementById('location-input');
            if (!input || !window.google || !google.maps || !google.maps.places) return;

            const ac = new google.maps.places.Autocomplete(input, {
                fields: ['place_id', 'geometry', 'formatted_address', 'name'],
                types: ['geocode'] // or ['establishment'] for venues
            });

            ac.addListener('place_changed', () => {
                const place = ac.getPlace();
                if (!place || !place.geometry) return;

                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                document.getElementById('location_place_id').value = place.place_id || '';
                document.getElementById('location_lat').value = lat;
                document.getElementById('location_lng').value = lng;

                // Normalize what the user sees
                if (place.formatted_address) {
                    input.value = place.formatted_address;
                }
            });
        };
    </script>

    {{-- Load Google Maps Places only if a key is configured --}}
    @if (config('services.google.maps_key'))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google.maps_key')) }}&libraries=places&callback=initPlaces" async defer></script>
    @else
        <div class="max-w-2xl mx-auto mt-4 text-yellow-700 bg-yellow-50 border border-yellow-200 p-3 rounded">
            Google Maps key is not configured. Add <code>GOOGLE_MAPS_API_KEY</code> to your .env file.
        </div>
    @endif
</x-app-layout>
