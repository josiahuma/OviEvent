<x-app-layout>
    @php
    // Build a secure absolute URL for the OG image (required by FB/Twitter)
    $ogPath = $event->banner_url ?: ($event->avatar_url ?: null);

    // If coming from the "public" disk, Storage::url() returns "/storage/…"
    // Wrap with secure_url() so it becomes "https://your-domain/storage/…"
    $ogImage = $ogPath
        ? secure_url(\Illuminate\Support\Facades\Storage::disk('public')->url($ogPath))
        : secure_asset('images/og-default.jpg'); // make sure this file exists in /public/images

    $ogTitle = $event->name;
    $plainDesc = trim(preg_replace('/\s+/', ' ', strip_tags($event->description ?? '')));
    $ogDesc = \Illuminate\Support\Str::limit($plainDesc ?: 'View event details and register.', 160, '…');
    @endphp

    @section('title', $ogTitle)

    @section('meta')
        <meta property="og:type" content="article">
        <meta property="og:site_name" content="{{ config('app.name', 'ovievent') }}">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDesc }}">
        <meta property="og:url" content="{{ secure_url(request()->path()) }}">

        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:secure_url" content="{{ $ogImage }}">
        <meta property="og:image:alt" content="{{ $ogTitle }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDesc }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
    @parent
    @endsection


    {{-- Hero --}}
    <div class="w-full bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto">
            <div class="relative rounded-b-2xl overflow-hidden">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $event->name }}" class="w-full h-[320px] md:h-[420px] object-cover" loading="lazy" decoding="async">
                @else
                    <div class="w-full h-[320px] md:h-[420px] bg-gradient-to-br from-slate-200 to-slate-100"></div>
                @endif

                <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                    @if ($event->category)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/90 text-gray-800 shadow">
                            {{ $event->category }}
                        </span>
                    @endif
                    @if ($event->is_promoted)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-500 text-white shadow">
                            Featured
                        </span>
                    @endif
                </div>

                <div class="absolute top-4 right-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $isFree ? 'bg-emerald-500' : 'bg-black/80' }} text-white shadow">
                        {{ $priceLabel }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left --}}
            <div class="lg:col-span-2 space-y-6">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $event->name }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                        @if ($event->organizer)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4 0-8 2-8 6v1h16v-1c0-4-4-6-8-6z"/></svg>
                                Organized by <span class="font-medium text-gray-800">{{ $event->organizer }}</span>
                            </span>
                        @endif
                        @if ($event->location)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.686 2 6 4.686 6 8c0 4.418 6 12 6 12s6-7.582 6-12c0-3.314-2.686-6-6-6zm0 8.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
                                {{ $event->location }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Sessions --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Dates & Sessions</h2>
                    @if ($event->sessions->count())
                        <ul class="mt-3 divide-y divide-gray-100">
                            @foreach ($event->sessions as $s)
                                <li class="py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center h-9 w-9 rounded-full bg-indigo-50">
                                            <svg class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1z"/><path d="M3 10h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8z"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $s->session_name }}</div>
                                            <div class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($s->session_date)->format('D, d M Y · g:ia') }}</div>
                                        </div>
                                    </div>
                                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-700">Add to calendar</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-2 text-sm text-gray-600">Schedule TBA.</p>
                    @endif
                </div>

                {{-- Description --}}
                @if ($event->description)
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">About this event</h2>
                        <div class="prose max-w-none mt-2 text-gray-700">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Tags --}}
                @if (!empty($tags))
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">Tags</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($tags as $tag)
                                <span class="px-3 py-1 text-xs rounded-full bg-blue-50 text-blue-700 border border-blue-100">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right column --}}
            <div class="lg:col-span-1">
                <div class="lg:sticky lg:top-6 space-y-6">
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-600">Ticket</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $priceLabel }}</div>
                            </div>
                            @if ($image)
                                <img src="{{ $image }}" alt="" class="h-12 w-12 rounded-lg object-cover">
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                            <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                                <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.686 2 6 4.686 6 8c0 4.418 6 12 6 12s6-7.582 6-12c0-3.314-2.686-6-6-6z"/><path d="M12 10.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
                                <span class="truncate">{{ $event->location ?? 'Online' }}</span>
                            </div>
                            <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                                <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1z"/><path d="M3 10h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8z"/></svg>
                                @if ($event->sessions->first())
                                    <span>{{ \Carbon\Carbon::parse($event->sessions->first()->session_date)->format('d M Y') }}</span>
                                @else
                                    <span>Dates TBA</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('events.register.create', $event) }}"
                           class="mt-5 w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                            Register
                        </a>

                        @if ($event->avatar_url)
                            <a href="{{ route('events.avatar', $event) }}"
                               class="mt-2 w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-amber-500 text-white font-medium hover:bg-amber-600 transition">
                                Create Personal Display Picture
                            </a>
                        @endif

                        <div class="mt-4 flex items-center gap-3">
                            <span class="text-xs text-gray-500">Share:</span>
                            <a class="text-gray-500 hover:text-gray-700" target="_blank"
                               href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($event->name) }}">X</a>
                            <a class="text-gray-500 hover:text-gray-700" target="_blank"
                               href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}">Facebook</a>
                            <a class="text-gray-500 hover:text-gray-700" target="_blank"
                               href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->fullUrl()) }}">LinkedIn</a>
                        </div>
                    </div>

                    @if ($event->organizer)
                        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-700 text-sm font-semibold">{{ strtoupper(substr($event->organizer, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Organizer</div>
                                    <div class="text-base font-medium text-gray-900">{{ $event->organizer }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
