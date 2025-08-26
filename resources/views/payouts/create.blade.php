<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Request payout — {{ $event->name }}
            </h2>
            <a href="{{ route('payouts.index') }}" class="text-sm text-gray-600 hover:text-gray-800 underline">My payouts</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <form method="POST" action="{{ route('payouts.store', $event) }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Amount (after commission)</label>
                    <input type="text" value="£{{ number_format($amount/100,2) }} {{ $currency }}"
                           class="mt-1 w-full rounded-lg border-gray-300 bg-gray-100 text-gray-700" disabled>
                    <input type="hidden" name="amount" value="{{ $amount }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account holder name</label>
                        <input name="account_name" required class="mt-1 w-full rounded-lg border-gray-300" />
                        @error('account_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sort code</label>
                        <input name="sort_code" required placeholder="12-34-56" class="mt-1 w-full rounded-lg border-gray-300" />
                        @error('sort_code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account number</label>
                        <input name="account_number" required placeholder="12345678" class="mt-1 w-full rounded-lg border-gray-300" />
                        @error('account_number')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">IBAN (optional)</label>
                        <input name="iban" placeholder="GB29NWBK60161331926819" class="mt-1 w-full rounded-lg border-gray-300" />
                        @error('iban')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center w-full md:w-auto px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                        Submit payout request
                    </button>
                </div>

                <p class="text-xs text-gray-500">
                    Your payout will show as <strong>processing</strong> until an admin marks it as paid.
                </p>
            </form>
        </div>
    </div>
</x-app-layout>
