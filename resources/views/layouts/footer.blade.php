<footer class="bg-[#ff5757] text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Top CTA --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 border-b border-white/15 pb-6">
            <h3 class="text-xl font-semibold text-white">
                Host your next event on <span class="font-bold">Eventib</span>
            </h3>
            <div class="flex items-center gap-3">
                <a href="{{ route('events.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-white/60 px-4 py-2 text-sm font-medium hover:bg-white hover:text-[#ff5757] focus:outline-none focus:ring-2 focus:ring-white/70 transition">
                    Create an event
                </a>
                <a href="{{ route('pricing') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-4 py-2 text-sm font-medium hover:border-white/70 focus:outline-none focus:ring-2 focus:ring-white/70 transition">
                    See pricing
                </a>
            </div>
        </div>

        {{-- Main links --}}
        <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-8">
            {{-- Brand + social --}}
            <div class="lg:col-span-2 space-y-3">
                <a href="{{ route('homepage') }}" class="inline-block">
                    {{-- If you have a white logo, swap the text with your <img> --}}
                    <span class="text-2xl font-extrabold tracking-tight">Eventib</span>
                </a>
                <p class="text-white/80 text-sm leading-relaxed">
                    Create, promote, and sell tickets for events worldwide.
                </p>
                <div class="flex items-center gap-3 pt-1">
                    <a href="https://twitter.com" target="_blank" rel="noopener" class="hover:opacity-90" aria-label="X">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2H21l-6.52 7.46L22 22h-6.9l-4.53-6.01L5.2 22H2.44l7.05-8.06L2 2h7l4.13 5.6L18.24 2Zm-2.41 18h1.9L8.28 4h-1.9l9.44 16Z"/></svg>
                    </a>
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="hover:opacity-90" aria-label="Facebook">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 22v-8h3l1-4h-4V7.5c0-1.16.32-1.95 2-1.95h2V2.1C16.67 2.04 15.34 2 14 2c-3.2 0-5 1.9-5 5.4V10H6v4h3v8h4z"/></svg>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="hover:opacity-90" aria-label="Instagram">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5A6.5 6.5 0 1 1 5.5 14 6.5 6.5 0 0 1 12 7.5Zm0 2A4.5 4.5 0 1 0 16.5 14 4.5 4.5 0 0 0 12 9.5Zm5.25-3.25a1.25 1.25 0 1 1-1.25 1.25 1.25 1.25 0 0 1 1.25-1.25Z"/></svg>
                    </a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener" class="hover:opacity-90" aria-label="LinkedIn">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 9H3v12h3V9Zm.34-4.5a1.84 1.84 0 1 1-3.68 0 1.84 1.84 0 0 1 3.68 0ZM21 21h-3v-6.5c0-1.55-.55-2.6-1.93-2.6A2.09 2.09 0 0 0 13 13.1V21h-3V9h3v1.62A3.34 3.34 0 0 1 15.86 9c2.36 0 5.14 1.39 5.14 5.74V21Z"/></svg>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener" class="hover:opacity-90" aria-label="YouTube">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M21.8 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.7.5A3 3 0 0 0 2.2 8.2 31 31 0 0 0 1.7 12a31 31 0 0 0 .5 3.8 3 3 0 0 0 2.1 2.1c1.7.5 7.7.5 7.7.5s6 0 7.7-.5a3 3 0 0 0 2.1-2.1c.4-1.3.5-2.6.5-3.8a31 31 0 0 0-.5-3.8ZM10 14.8V9.2l4.8 2.8L10 14.8Z"/></svg>
                    </a>
                </div>
            </div>

            {{-- For Attendees --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white/90">For attendees</h4>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('homepage') }}" class="hover:underline">Find events</a></li>
                    <li><a href="{{ route('my.tickets') }}" class="hover:underline">Manage my tickets</a></li>
                    <li><a href="{{ route('how') }}" class="hover:underline">How it works</a></li>
                </ul>
            </div>

            {{-- For Organizers --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white/90">For organizers</h4>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('events.create') }}" class="hover:underline">Create an event</a></li>
                    <li><a href="{{ route('pricing') }}" class="hover:underline">Pricing</a></li>
                    <li><a href="{{ route('payouts.index') }}" class="hover:underline">Payouts</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white/90">Company</h4>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="mailto:hello@eventib.com" class="hover:underline">Contact</a></li>
                    <li><a href="#" class="hover:underline">About</a></li>
                    <li><a href="#" class="hover:underline">Blog</a></li>
                </ul>
            </div>

            {{-- Newsletter --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white/90">Stay in the loop</h4>
                <form class="mt-3" method="post" action="#">
                    @csrf
                    <label for="newsletter_email" class="sr-only">Email</label>
                    <div class="flex items-center gap-2">
                        <input
                            id="newsletter_email"
                            name="email"
                            type="email"
                            placeholder="Your email"
                            class="w-full rounded-lg bg-white/10 placeholder-white/60 text-white border border-white/20 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/70"
                        />
                        <button type="submit"
                                class="inline-flex items-center rounded-lg border border-white/60 px-3 py-2 text-sm font-medium hover:bg-white hover:text-[#ff5757] focus:outline-none focus:ring-2 focus:ring-white/70 transition">
                            Subscribe
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-8 pt-6 border-t border-white/10 text-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-white/80">© {{ date('Y') }} Eventib. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="#" class="text-white/80 hover:text-white">Terms</a>
                <a href="#" class="text-white/80 hover:text-white">Privacy</a>
                <a href="#" class="text-white/80 hover:text-white">Cookies</a>
            </div>
        </div>
    </div>
</footer>
