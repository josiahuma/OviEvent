<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Email registrants — {{ $event->name }}
            </h2>
            <a href="{{ route('events.registrants', $event) }}" class="text-sm text-gray-600 hover:text-gray-800 underline">
                Back to registrants
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-amber-50 text-amber-800 border border-amber-200">
                {{ session('error') }}
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
            <p class="text-sm text-gray-600 mb-4">Sending to <strong>{{ $count }}</strong> registrant{{ $count===1?'':'s' }}.</p>

            <form method="POST" action="{{ route('events.registrants.email.send', $event) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="mt-1 w-full rounded-lg border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea name="message" rows="10" class="mt-1 w-full rounded-lg border-gray-300" placeholder="Write your message…" required>{{ old('message') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Basic formatting is supported. Links will be auto-detected.</p>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('events.registrants', $event) }}" class="text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                        Send email
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
