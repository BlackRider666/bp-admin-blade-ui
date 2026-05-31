<button type="button"
        @click="$store.ui.toggleTheme()"
        :title="$store.ui.theme === 'dark' ? '{{ __('bpadmin::ui.switch_to_light') }}' : '{{ __('bpadmin::ui.switch_to_dark') }}'"
        aria-label="Toggle theme"
        class="inline-flex items-center justify-center h-10 w-10 rounded-xl bp-btn-ghost">
    {{-- Sun icon — visible when current theme is dark (clicking switches to light) --}}
    <svg x-show="$store.ui.theme === 'dark'" x-cloak
         class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3v1.5M12 19.5V21M4.22 4.22l1.06 1.06M18.72 18.72l1.06 1.06M3 12h1.5M19.5 12H21M4.22 19.78l1.06-1.06M18.72 5.28l1.06-1.06M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    {{-- Moon icon — visible when current theme is light (clicking switches to dark) --}}
    <svg x-show="$store.ui.theme === 'light'" x-cloak
         class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
    </svg>
</button>
