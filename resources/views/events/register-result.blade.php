<x-app-layout>
    @php
        $palette = [
            'success' => ['bg' => 'bg-emerald-50', 'bd' => 'border-emerald-200', 'tx' => 'text-emerald-800'],
            'error'   => ['bg' => 'bg-rose-50',    'bd' => 'border-rose-200',    'tx' => 'text-rose-700'],
            'warning' => ['bg' => 'bg-amber-50',   'bd' => 'border-amber-200',   'tx' => 'text-amber-800'],
            'info'    => ['bg' => 'bg-blue-50',    'bd' => 'border-blue-200',    'tx' => 'text-blue-800'],
        ][$state ?? 'info'];
        $image = $event->banner_url
            ? asset('storage/' . $event->banner_url)
            : ($event->avatar_url ? asset('storage/' . $event->avatar_url) : null);
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title ?? 'Registration' }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="rounded-2xl border {{ $palette['bd'] }} {{ $palette['bg'] }} p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <div class="shrink-0">
                    @if(($state ?? 'info') === 'success')
                        <svg class="h-8 w-8 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12.75 11.25 15 15 9.75l1.5 1.5L11.25 18 7.5 14.25l1.5-1.5z"/><path fill-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM4 12a8 8 0 1 1 16 0 8 8 0 0 1-16 0z" clip-rule="evenodd"/></svg>
                    @elseif(($state ?? 'info') === 'warning')
                        <svg class="h-8 w-8 text-amber-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 9v4m0 4h.01M10.29 3.86a2 2 0 0 1 3.42 0l8.18 13.82A2 2 0 0 1 20.18 21H3.82a2 2 0 0 1-1.71-3.32L10.29 3.86z"/></svg>
                    @elseif(($state ?? 'info') === 'error')
                        <svg class="h-8 w-8 text-rose-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 9v4m0 4h.01M10.29 3.86a2 2 0 0 1 3.42 0l8.18 13.82A2 2 0 0 1 20.18 21H3.82a2 2 0 0 1-1.71-3.32L10.29 3.86z"/></svg>
                    @else
                        <svg class="h-8 w-8 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/></svg>
                    @endif
                </div>
                <div>
                    <h1 class="text-lg font-semibold {{ $palette['tx'] }}">
                        {{ $title ?? 'Registration' }}
                    </h1>
                    @if(!empty($message))
                        <p class="mt-1 text-sm {{ $palette['tx'] }}">{{ $message }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 rounded-xl bg-white border border-gray-200 p-4 flex items-center gap-4">
                @if($image)
                    <img src="{{ $image }}" alt="" class="h-16 w-16 rounded-lg object-cover">
                @endif
                <div>
                    <div class="text-sm text-gray-500">Event</div>
                    <div class="text-base font-medium text-gray-900">{{ $event->name }}</div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('events.show', $event->id) }}"
                   class="inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                    Back to event
                </a>
                <a href="{{ route('events.register.create', $event->id) }}"
                   class="inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-gray-100 text-gray-800 hover:bg-gray-200">
                    Register another attendee
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
