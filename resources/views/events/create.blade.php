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
                    <option value="Technology">Techology</option>
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

            <!-- Ticket Cost -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Ticket Cost (£)</label>
                <input type="number" name="ticket_cost" step="0.01" class="w-full border rounded p-2">
            </div>

            <!-- Event Avatar -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Event Avatar</label>
                <input type="file" name="avatar" class="w-full border rounded p-2">
            </div>

            <!-- Event Banner -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Event Banner</label>
                <input type="file" name="banner" class="w-full border rounded p-2">
            </div>

            <!-- Event Sessions -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Event Sessions</label>
                <div id="sessions-wrapper">
                    <div class="flex gap-2 mb-2 session-item">
                        <input type="text" name="sessions[0][name]" placeholder="Session Name" class="border p-2 rounded w-1/3" required>
                        <input type="date" name="sessions[0][date]" class="border p-2 rounded w-1/3" required>
                        <input type="time" name="sessions[0][time]" class="border p-2 rounded w-1/3" required>
                        <button type="button" class="remove-session bg-red-500 text-white px-2 py-1 rounded">X</button>
                    </div>
                </div>
                <button type="button" id="add-session" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded">+ Add Session</button>
            </div>

            <!-- Submit -->
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Create Event
            </button>
        </form>
    </div>

    <script>
        // Sessions UI
        let sessionIndex = 1;
        document.getElementById('add-session').addEventListener('click', function() {
            const wrapper = document.getElementById('sessions-wrapper');
            const newSession = `
                <div class="flex gap-2 mb-2 session-item">
                    <input type="text" name="sessions[${sessionIndex}][name]" placeholder="Session Name" class="border p-2 rounded w-1/3" required>
                    <input type="date" name="sessions[${sessionIndex}][date]" class="border p-2 rounded w-1/3" required>
                    <input type="time" name="sessions[${sessionIndex}][time]" class="border p-2 rounded w-1/3" required>
                    <button type="button" class="remove-session bg-red-500 text-white px-2 py-1 rounded">X</button>
                </div>`;
            wrapper.insertAdjacentHTML('beforeend', newSession);
            sessionIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-session')) {
                e.target.closest('.session-item').remove();
            }
        });

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
