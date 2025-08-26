<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My payouts
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800 underline">Back to dashboard</a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b text-sm text-gray-600">
                {{ $payouts->total() }} {{ Str::plural('payout', $payouts->total()) }}
            </div>

            <div class="divide-y">
                @forelse($payouts as $p)
                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 truncate">
                                {{ $p->event->name ?? 'Event deleted' }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Amount: £{{ number_format($p->amount/100, 2) }} {{ strtoupper($p->currency) }}
                                <span class="mx-2">•</span>
                                Sort code: {{ $p->sort_code }} — Account: {{ $p->account_number }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">{{ $p->created_at?->format('d M Y, g:ia') }}</div>
                            <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs
                                @if($p->status === 'paid') bg-emerald-100 text-emerald-700
                                @elseif($p->status === 'canceled') bg-rose-100 text-rose-700
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ ucfirst($p->status) }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-600">No payout requests yet.</div>
                @endforelse
            </div>

            <div class="p-4">
                {{ $payouts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
