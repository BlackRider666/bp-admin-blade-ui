@extends('bpadmin::layout.auth')

@section('content')
<div class="bp-glass rounded-2xl p-8 sm:p-10 animate-fade-up">
    <div class="flex flex-col items-center mb-8">
        <div class="relative mb-5">
            <div class="absolute -inset-2 rounded-full bg-bp-primary/40 blur-2xl opacity-60"></div>
            <div class="relative h-14 w-14 rounded-2xl bg-primary-sheen flex items-center justify-center shadow-glow-primary">
                <span class="font-display font-bold text-white text-lg tracking-tight select-none">
                    {{ mb_strtoupper(mb_substr(config('bpadmin.name', 'BPAdmin'), 0, 2)) }}
                </span>
            </div>
        </div>
        <h1 class="font-display text-3xl font-bold bp-gradient-text text-center tracking-tight">
            {{ config('bpadmin.name', 'BPAdmin') }}
        </h1>
        <p class="mt-2 text-sm text-bp-muted">{{ __('bpadmin::auth.login.title') }}</p>
    </div>

    <form method="POST" action="{{ route('bpadmin.auth.login.post') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-bp-gray-500 mb-2">
                {{ __('bpadmin::auth.login.email') }}
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-bp-muted pointer-events-none">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9"/>
                    </svg>
                </span>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="email"
                       placeholder="you@example.com"
                       class="block w-full rounded-xl border bg-bp-surface pl-10 pr-3 py-2.5 text-sm @error('email') border-red-500/60 @enderror">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-bp-gray-500 mb-2">
                {{ __('bpadmin::auth.login.password') }}
            </label>
            <div class="relative" x-data="{ show: false }">
                <span class="absolute inset-y-0 left-3 flex items-center text-bp-muted pointer-events-none">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <input :type="show ? 'text' : 'password'"
                       id="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="block w-full rounded-xl border bg-bp-surface pl-10 pr-10 py-2.5 text-sm @error('password') border-red-500/60 @enderror">
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-3 flex items-center text-bp-muted hover:text-bp-gray-700 transition-colors">
                    <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411M3 3l18 18"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit"
                class="bp-btn-primary w-full rounded-xl text-sm font-semibold py-3 px-4 flex items-center justify-center gap-2 group">
            <span>{{ __('bpadmin::auth.login.submit') }}</span>
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>

    <div class="mt-7 flex items-center justify-center gap-2 text-[11px] text-bp-muted uppercase tracking-widest">
        <span class="h-px w-8 bg-bp-border"></span>
        <span>Secure admin</span>
        <span class="h-px w-8 bg-bp-border"></span>
    </div>
</div>
@endsection
