<x-guest-layout>
    <div class="min-h-[80vh] flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="bg-white border border-gray-200 shadow-sm rounded-2xl p-6 sm:p-8">
                {{-- Header --}}
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Create your account</h1>
                    <p class="mt-1 text-sm text-gray-600">Join in and start registering for events</p>
                </div>

                {{-- Social sign-up --}}
                <div class="space-y-3">
                    <a href="{{ route('oauth.redirect','google') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-5 w-5" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42v-.1H24v7.2h11.3c-1.6 4.5-5.9 7.7-11.3 7.7-6.4 0-11.7-5.2-11.7-11.7S17.6 12 24 12c3 0 5.7 1.1 7.7 3l5-5C33.5 6.6 28.9 4.8 24 4.8 13.7 4.8 5.3 13.2 5.3 23.5S13.7 42.2 24 42.2 42.7 33.8 42.7 23.5c0-1-.1-2-.3-3z"/><path fill="#FF3D00" d="M6.3 14.7l5.9 4.3C13.9 15.1 18.6 12 24 12c3 0 5.7 1.1 7.7 3l5-5C33.5 6.6 28.9 4.8 24 4.8 16.4 4.8 9.8 9.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 42.2c5.3 0 9.8-1.8 13-4.8l-6-4.9c-2 1.4-4.6 2.3-7 2.3-5.4 0-10-3.4-11.6-8.1l-5.9 4.6C9.8 37.8 16.4 42.2 24 42.2z"/><path fill="#1976D2" d="M43.6 20.5H42v-.1H24v7.2h11.3c-.8 2.2-2.2 4-4 5.3.1-.1 6 4.9 6 4.9l.4.3C40.9 35.4 42.7 29.8 42.7 23.5c0-1-.1-2-.3-3z"/></svg>
                        Continue with Google
                    </a>
                </div>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="border-t border-gray-200"></div>
                    <span class="absolute inset-0 -top-3 mx-auto w-max px-3 bg-white text-xs text-gray-500">
                        or sign up with email
                    </span>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <div class="relative mt-1">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4 0-8 2-8 6v1h16v-1c0-4-4-6-8-6z"/></svg>
                            <x-text-input
                                id="name"
                                type="text"
                                name="name"
                                :value="old('name')"
                                required
                                autofocus
                                autocomplete="name"
                                class="block w-full rounded-lg border-gray-300 pl-10 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <div class="relative mt-1">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M2 6.75A2.75 2.75 0 0 1 4.75 4h14.5A2.75 2.75 0 0 1 22 6.75v10.5A2.75 2.75 0 0 1 19.25 20H4.75A2.75 2.75 0 0 1 2 17.25V6.75zm2.4-.25L12 11.1l7.6-4.6H4.4z"/></svg>
                            <x-text-input
                                id="email"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autocomplete="username"
                                class="block w-full rounded-lg border-gray-300 pl-10 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Password --}}
                    <div x-data="{ show: false }">
                        <x-input-label for="password" :value="__('Password')" />
                        <div class="relative mt-1">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V6a5 5 0 0 0-5-5zm-3 8V6a3 3 0 1 1 6 0v3H9z"/></svg>
                            <input
                                id="password"
                                x-bind:type="show ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-lg border-gray-300 pl-10 pr-10 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                            />
                            <button type="button"
                                    @click="show = !show"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    aria-label="Toggle password visibility">
                                <svg x-show="!show" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3.27 2 2 3.27 5.11 6.4A12.27 12.27 0 0 0 2 12s3 7 10 7a10.8 10.8 0 0 0 5.6-1.6l3.13 3.13L22 19.73 3.27 2zM12 17c-5.3 0-8-5-8-5a13.6 13.6 0 0 1 3.6-3.73l1.55 1.55A5 5 0 0 0 12 17z"/><path d="M12 7a5 5 0 0 1 5 5c0 .6-.1 1.2-.3 1.7l3 3C21.8 15.7 22 14 22 12c0 0-3-7-10-7-1.8 0-3.4.4-4.8 1.1l2.1 2.1A5 5 0 0 1 12 7z"/></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Confirm Password --}}
                    <div x-data="{ show2: false }">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <div class="relative mt-1">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V6a5 5 0 0 0-5-5zm-3 8V6a3 3 0 1 1 6 0v3H9z"/></svg>
                            <input
                                id="password_confirmation"
                                x-bind:type="show2 ? 'text' : 'password'"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-lg border-gray-300 pl-10 pr-10 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                            />
                            <button type="button"
                                    @click="show2 = !show2"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    aria-label="Toggle password visibility">
                                <svg x-show="!show2" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg>
                                <svg x-show="show2" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3.27 2 2 3.27 5.11 6.4A12.27 12.27 0 0 0 2 12s3 7 10 7a10.8 10.8 0 0 0 5.6-1.6l3.13 3.13L22 19.73 3.27 2zM12 17c-5.3 0-8-5-8-5a13.6 13.6 0 0 1 3.6-3.73l1.55 1.55A5 5 0 0 0 12 17z"/><path d="M12 7a5 5 0 0 1 5 5c0 .6-.1 1.2-.3 1.7l3 3C21.8 15.7 22 14 22 12c0 0-3-7-10-7-1.8 0-3.4.4-4.8 1.1l2.1 2.1A5 5 0 0 1 12 7z"/></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    {{-- Terms (optional; remove if not needed) --}}
                    {{-- <p class="text-xs text-gray-500">By creating an account, you agree to our <a href="#" class="underline">Terms</a> and <a href="#" class="underline">Privacy Policy</a>.</p> --}}

                    {{-- Submit --}}
                    <x-primary-button class="w-full justify-center">
                        {{ __('Register') }}
                    </x-primary-button>
                </form>

                {{-- Footer --}}
                <p class="mt-6 text-center text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-700">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
