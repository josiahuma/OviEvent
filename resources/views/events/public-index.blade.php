<x-app-layout>
    <x-slot name="header">
        @php
            $totalCount = ($featured->total() ?? 0) + ($upcoming->total() ?? 0) + ($past->total() ?? 0);
        @endphp
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                Discover Events
            </h2>
            <div class="text-sm text-gray-500">
                {{ $totalCount }} {{ \Illuminate\Support\Str::plural('event', $totalCount) }}
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="{
            query: @js($q ?? ''),
            category: @js($category ?? ''),
            price: @js($price ?? 'all'),
            startDate: @js($startDate ?? ''),
            endDate: @js($endDate ?? ''),
            submitFilters() { $refs.filterForm.submit(); },
            reset() { this.category=''; this.price='all'; this.startDate=''; this.endDate=''; $refs.filterForm.submit(); }
         }">

        {{-- Hero Search --}}
        <form method="GET" action="{{ route('homepage') }}" class="mb-6">
            <div class="relative bg-gradient-to-r from-indigo-300 to-violet-400 rounded-2xl p-5 sm:p-6 shadow-md">
                <div class="bg-white rounded-xl p-2 sm:p-3 shadow flex items-stretch gap-2">
                    <div class="flex-1 flex items-center gap-2 px-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 2a8 8 0 0 1 6.32 12.9l4.39 4.39-1.41 1.41-4.39-4.39A8 8 0 1 1 10 2zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12z"/>
                        </svg>
                        <input
                            name="q"
                            value="{{ $q }}"
                            placeholder="Search for events, organizers, places…"
                            class="w-full border-0 focus:ring-0 text-base placeholder-gray-400"
                        />
                    </div>
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                        Search
                    </button>
                </div>

                {{-- keep existing filters when searching --}}
                <input type="hidden" name="category" value="{{ $category }}">
                <input type="hidden" name="price" value="{{ $price }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">

                @if($q)
                    <div class="mt-2 text-white/90 text-sm">
                        Showing results for: <span class="font-medium">“{{ $q }}”</span>
                        <a href="{{ route('homepage', array_filter(['category'=>$category,'price'=>$price,'start_date'=>$startDate,'end_date'=>$endDate])) }}"
                           class="underline ml-2">Clear search</a>
                    </div>
                @endif
            </div>
        </form>

        {{-- Filter Bar --}}
        <form x-ref="filterForm" method="GET" action="{{ route('homepage') }}" class="mb-6">
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" x-model="category" @change="submitFilters()" class="w-full rounded-lg border-gray-300">
                            <option value="">All</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" @selected(($category ?? '') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Price --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <select name="price" x-model="price" @change="submitFilters()" class="w-full rounded-lg border-gray-300">
                            <option value="all">All</option>
                            <option value="free">Free</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    {{-- Start Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start date</label>
                        <input type="date" name="start_date" x-model="startDate" @change="submitFilters()" class="w-full rounded-lg border-gray-300" />
                    </div>

                    {{-- End Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End date</label>
                        <input type="date" name="end_date" x-model="endDate" @change="submitFilters()" class="w-full rounded-lg border-gray-300" />
                    </div>
                </div>

                {{-- keep search when changing filters --}}
                <input type="hidden" name="q" :value="query">

                <div class="mt-3 flex items-center gap-2 flex-wrap">
                    @if(($category ?? null) || ($price ?? 'all') !== 'all' || ($startDate ?? null) || ($endDate ?? null))
                        <span class="text-xs text-gray-500">Active:</span>
                    @endif

                    @if($category ?? false)
                        <span class="text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                            Category: {{ $category }}
                        </span>
                    @endif
                    @if(($price ?? 'all') !== 'all')
                        <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                            Price: {{ ucfirst($price) }}
                        </span>
                    @endif
                    @if($startDate ?? false)
                        <span class="text-xs px-2 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">
                            From: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                        </span>
                    @endif
                    @if($endDate ?? false)
                        <span class="text-xs px-2 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100">
                            To: {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                        </span>
                    @endif

                    <button type="button" @click="reset()"
                            class="ml-auto text-sm text-gray-600 hover:text-gray-800 underline">
                        Reset filters
                    </button>
                </div>
            </div>
        </form>

        {{-- ===== Featured ===== --}}
        @if ($featured->count())
            <section class="mb-10">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xl font-semibold text-gray-900">Featured</h3>
                    <span class="text-sm text-gray-500">{{ $featured->total() }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($featured as $event)
                        @include('events.partials._event_card', ['event' => $event, 'forceFeatured' => true])
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $featured->withQueryString()->links() }}
                </div>
            </section>
        @endif

        {{-- ===== Upcoming ===== --}}
        @if ($upcoming->count())
            <section class="mb-10">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xl font-semibold text-gray-900">Upcoming</h3>
                    <span class="text-sm text-gray-500">{{ $upcoming->total() }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($upcoming as $event)
                        @include('events.partials._event_card', ['event' => $event])
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $upcoming->withQueryString()->links('pagination::tailwind', ['paginator' => $upcoming]) }}
                </div>
            </section>
        @endif

        {{-- ===== Past ===== --}}
        @if ($past->count())
            <section>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xl font-semibold text-gray-900">Past events</h3>
                    <span class="text-sm text-gray-500">{{ $past->total() }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($past as $event)
                        @include('events.partials._event_card', ['event' => $event])
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $past->withQueryString()->links('pagination::tailwind', ['paginator' => $past]) }}
                </div>
            </section>
        @endif

        @if (!$featured->count() && !$upcoming->count() && !$past->count())
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                <h3 class="text-lg font-semibold text-gray-800">No events match your search</h3>
                <p class="mt-1 text-sm text-gray-500">Try a different keyword or adjust the filters.</p>
            </div>
        @endif
    </div>
</x-app-layout>
