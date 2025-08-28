@php
    $image = $event->banner_url
        ? asset('storage/' . $event->banner_url)
        : ($event->avatar_url ? asset('storage/' . $event->avatar_url) : null);

    $now = \Carbon\Carbon::now();
    $nextSession = $event->sessions
        ->firstWhere(fn($s) => \Carbon\Carbon::parse($s->session_date)->gte($now))
        ?? $event->sessions->first();

    // robust tags parsing
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

    $isFree = ($event->ticket_cost ?? 0) == 0;
    $priceLabel = $isFree ? 'Free' : '£' . number_format($event->ticket_cost, 2);

    // Featured ONLY if forced or explicitly promoted
    $isFeatured = ($forceFeatured ?? false) || ($event->is_promoted ?? false);
@endphp

<a href="{{ route('events.show', $event) }}"
   class="group block bg-white rounded-2xl overflow-hidden shadow-sm ring-1 ring-gray-200 hover:shadow-lg hover:ring-gray-300 transition-all duration-200 relative">

    {{-- In-bounds featured pill (no clipping/flicker) --}}
    @if ($isFeatured)
        <div class="absolute top-3 left-3 z-10">
            <span class="bg-amber-500/95 text-white text-xs font-semibold px-2.5 py-1 rounded-md shadow">
                Featured
            </span>
        </div>
    @endif

    <div class="relative">
        @if ($image)
            <img src="{{ $image }}"
                 alt="{{ $event->name }}"
                 class="h-48 w-full object-cover"
                 loading="lazy"
                 decoding="async">
        @else
            <div class="h-48 w-full bg-gradient-to-br from-slate-200 to-slate-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M4 6a2 2 0 0 1 2-2h2a1 1 0 1 1 0 2H6v12h12v-2a1 1 0 1 1 2 0v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6z"/>
                    <path d="M18 8h-4a2 2 0 0 0-2 2v4h2v-3a1 1 0 1 1 2 0v3h2v-5a1 1 0 1 1 2 0v5h1a1 1 0 1 0 0-2h-1V8a2 2 0 0 0-2-2z"/>
                </svg>
            </div>
        @endif

        {{-- Category badge --}}
        @if ($event->category)
            <span class="absolute top-3 left-3 translate-y-8 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-white/90 text-gray-800 shadow">
                {{ $event->category }}
            </span>
        @endif

        {{-- Price badge --}}
        <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
            {{ $isFree ? 'bg-emerald-500 text-white' : 'bg-black/80 text-white' }}">
            {{ $priceLabel }}
        </span>
    </div>

    <div class="p-4">
        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-gray-800 line-clamp-1">
            {{ $event->name }}
        </h3>

        <div class="mt-2 flex items-center text-sm text-gray-600 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1z"/>
                <path d="M3 10h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8z"/>
            </svg>
            @if ($nextSession)
                <span>
                    {{ \Carbon\Carbon::parse($nextSession->session_date)->format('D, d M Y · g:ia') }}
                </span>
            @else
                <span>Schedule TBA</span>
            @endif
        </div>

        @if ($event->location)
            <div class="mt-1 flex items-center text-sm text-gray-600 gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8.686 2 6 4.686 6 8c0 4.418 6 12 6 12s6-7.582 6-12c0-3.314-2.686-6-6-6zm0 8.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
                </svg>
                <span class="line-clamp-1">{{ $event->location }}</span>
            </div>
        @endif

        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4 0-8 2-8 6v1h16v-1c0-4-4-6-8-6z"/>
                </svg>
                <span>{{ $event->organizer ?? 'Organizer' }}</span>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                {{ $event->sessions->count() }} {{ \Illuminate\Support\Str::plural('session', $event->sessions->count()) }}
            </span>
        </div>

        @if (!empty($tags))
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($tags as $tag)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-50 text-blue-700 border border-blue-100">#{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>
</a>
