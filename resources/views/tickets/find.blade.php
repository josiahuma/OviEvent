<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Find your booking — {{ $event->name }}
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
            <p class="text-sm text-gray-600 mb-4">
                Enter the email you used to register. We’ll send you a secure link to manage your booking.
            </p>

            <form method="POST" action="{{ route('events.ticket.sendlink', $event) }}">
                @csrf
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" required
                       value="{{ old('email') }}"
                       class="mt-1 w-full rounded-lg border-gray-300" />
                <div class="mt-4 flex items-center justify-end gap-3">
                    <a href="{{ route('events.show', $event) }}" class="text-sm text-gray-600 hover:text-gray-800">Back</a>
                    <button class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                        Send link
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
