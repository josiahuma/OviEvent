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

        <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block text-gray-700 font-semibold">Event Name</label>
                    <input type="text" name="name" value="{{ old('name', $event->name) }}" class="w-full border rounded p-2" required>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold">Organizer</label>
                        <input type="text" name="organizer" value="{{ old('organizer', $event->organizer) }}" class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold">Category</label>
                        <select name="category" class="w-full border rounded p-2">
                            <option value="">-- Select Category --</option>
                            @foreach (['Music','Tech','Business','Education','Sports'] as $cat)
                                <option value="{{ $cat }}" @selected(old('category', $event->category) === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold">Tags</label>
                    <select id="tags" name="tags[]" multiple class="w-full border rounded p-2">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag }}" selected>{{ $tag }}</option>
                        @endforeach
                    </select>
                    <small class="text-gray-500">Type and press enter to add a tag.</small>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold">Location</label>
                    <input type="text" name="location" value="{{ old('location', $event->location) }}" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold">Description</label>
                    <textarea name="description" rows="4" class="w-full border rounded p-2">{{ old('description', $event->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold">Ticket Cost (£)</label>
                        <input type="number" name="ticket_cost" step="0.01" value="{{ old('ticket_cost', $event->ticket_cost) }}" class="w-full border rounded p-2">
                    </div>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-gray-700 font-semibold">Event Avatar (replace)</label>
                            <input type="file" name="avatar" class="w-full border rounded p-2">
                            @if ($event->avatar_url)
                                <p class="text-xs text-gray-500 mt-1">Current: <span class="underline">{{ $event->avatar_url }}</span></p>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold">Event Banner (replace)</label>
                    <input type="file" name="banner" class="w-full border rounded p-2">
                    @if ($event->banner_url)
                        <p class="text-xs text-gray-500 mt-1">Current: <span class="underline">{{ $event->banner_url }}</span></p>
                    @endif
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Save Changes
                    </button>
                    <a href="{{ route('dashboard') }}" class="ml-3 text-gray-600 hover:text-gray-800 underline">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tom Select (tags) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
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
    </script>
</x-app-layout>
