@php
    $resolver = app(\BlackParadise\LaravelAdmin\Support\AvailableLocalesResolver::class);
    $locales  = $resolver->list();
    $current  = app()->getLocale();
@endphp

@if (count($locales) > 1)
<div class="relative" x-data="{ open: false }">
    <button type="button"
            @click="open = !open"
            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold uppercase tracking-wider text-bp-gray-700 hover:text-bp-gray-900">
        {{ strtoupper($current) }}
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open"
         @click.outside="open = false"
         x-transition
         class="absolute right-0 mt-1 bg-bp-surface border border-bp-border rounded shadow-md py-1 min-w-[80px] z-50"
         style="display: none;">
        @foreach ($locales as $locale)
            <form method="POST" action="{{ route('bpadmin.locale.switch') }}" class="block">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">
                <button type="submit"
                        class="w-full text-left px-3 py-1 text-xs font-semibold uppercase tracking-wider hover:bg-bp-gray-50 {{ $locale === $current ? 'text-bp-primary' : 'text-bp-gray-700' }}">
                    {{ strtoupper($locale) }}
                </button>
            </form>
        @endforeach
    </div>
</div>
@endif
