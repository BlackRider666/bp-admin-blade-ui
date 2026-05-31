@php
    $user   = auth()->user();
    $name   = $user?->name ?? $user?->email ?? 'Guest';
    $email  = $user?->email;
    $letter = strtoupper(substr($user?->name ?? $user?->email ?? 'A', 0, 1));
@endphp
<header class="relative bg-bp-surface/80 backdrop-blur-xl border-b border-bp-border h-[82px] flex items-center px-6 flex-shrink-0 z-10">
    <div class="flex-1 flex items-center gap-1 text-sm text-bp-muted min-w-0">
        @yield('breadcrumbs')
    </div>

    {{-- Quick actions --}}
    <div class="flex items-center gap-2 flex-shrink-0">
        <button type="button" title="Command palette (⌘K)"
                @click="$store.ui.openPalette()"
                class="inline-flex items-center gap-2 h-10 pl-3 pr-2 rounded-xl bp-btn-ghost text-xs">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <span class="hidden md:inline text-bp-muted">Search</span>
            <kbd class="hidden md:inline-flex items-center rounded-md border border-bp-border bg-bp-surface-2 px-1.5 py-0.5 text-[10px] font-mono-bp text-bp-muted">⌘K</kbd>
        </button>

        {{-- Theme toggle --}}
        @include('bpadmin::components.theme-toggle')

        {{-- Locale switcher --}}
        @include('bpadmin::components.locale-switcher')

        {{-- User dropdown --}}
        <div class="relative ml-1" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex items-center gap-3 h-10 pl-1.5 pr-3 rounded-full bp-btn-ghost select-none">
                <span class="relative flex h-7 w-7 items-center justify-center rounded-full bg-primary-sheen text-white text-xs font-display font-bold shadow-glow-primary">
                    {{ $letter }}
                </span>
                <span class="hidden sm:flex flex-col text-left leading-tight">
                    <span class="text-[13px] font-semibold text-bp-gray-800 truncate max-w-[140px]">{{ $name }}</span>
                    @if($email)
                        <span class="text-[10px] uppercase tracking-wider text-bp-muted truncate max-w-[140px]">{{ $email }}</span>
                    @endif
                </span>
                <svg :class="open ? 'rotate-180' : ''" class="h-3.5 w-3.5 text-bp-muted transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open"
                 x-cloak
                 @click.outside="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-64 bp-glass rounded-2xl py-2 z-30">
                <div class="px-4 pt-2 pb-3 border-b border-bp-border/70">
                    <p class="text-sm font-semibold text-bp-gray-800 truncate">{{ $name }}</p>
                    @if($email)
                        <p class="text-xs text-bp-muted truncate">{{ $email }}</p>
                    @endif
                </div>
                <div class="py-1.5">
                    <form method="POST" action="{{ route('bpadmin.auth.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 text-left px-4 py-2 text-sm text-bp-gray-700 hover:bg-white/5 hover:text-red-300 transition-colors rounded-lg mx-1">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>{{ __('bpadmin::auth.logout') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
