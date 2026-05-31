@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php $inputName = $name ?? $field->name(); $inputErrorKey = $errorKey ?? $field->name(); @endphp

<div class="relative">
    <span class="absolute inset-y-0 left-3 flex items-center text-bp-muted pointer-events-none">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </span>
    <input type="email"
           id="{{ $inputName }}"
           name="{{ $inputName }}"
           value="{{ $value }}"
           placeholder="name@example.com"
           autocomplete="email"
           inputmode="email"
           class="block w-full rounded-xl border border-bp-border bg-bp-surface pl-10 pr-3 py-2 text-sm text-bp-gray-500 focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none @error($inputErrorKey) border-red-300 @enderror">
</div>
