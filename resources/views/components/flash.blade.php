@php
    $hasSuccess = session('success');
    $hasWarning = session('warning');
    $hasError   = session('error') || $errors->any();
@endphp

@if($hasSuccess)
    <div x-data="{ show: true }" x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-init="setTimeout(() => show = false, 4500)"
         class="mx-6 mt-4 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200 backdrop-blur px-4 py-3 text-sm flex items-start gap-3">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/25 text-emerald-300 flex-shrink-0">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </span>
        <span class="flex-1">{{ session('success') }}</span>
        <button @click="show = false" class="text-emerald-400/70 hover:text-emerald-200 transition-colors text-lg leading-none">&times;</button>
    </div>
@endif

@if($hasWarning)
    <div x-data="{ show: true }" x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-init="setTimeout(() => show = false, 5500)"
         class="mx-6 mt-4 rounded-2xl border border-amber-500/30 bg-amber-500/10 text-amber-200 backdrop-blur px-4 py-3 text-sm flex items-start gap-3">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/25 text-amber-300 flex-shrink-0">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </span>
        <span class="flex-1">{{ session('warning') }}</span>
        <button @click="show = false" class="text-amber-400/70 hover:text-amber-200 transition-colors text-lg leading-none">&times;</button>
    </div>
@endif

@if($hasError)
    <div x-data="{ show: true }" x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mx-6 mt-4 rounded-2xl border border-red-500/30 bg-red-500/10 text-red-200 backdrop-blur px-4 py-3 text-sm flex items-start gap-3">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/25 text-red-300 flex-shrink-0 mt-0.5">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
        <div class="flex-1">
            @if(session('error'))
                <p>{{ session('error') }}</p>
            @endif
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        <button @click="show = false" class="text-red-400/70 hover:text-red-200 transition-colors text-lg leading-none">&times;</button>
    </div>
@endif
