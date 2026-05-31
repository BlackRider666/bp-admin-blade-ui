{{-- Global confirm modal backed by $store.confirm --}}
<div x-cloak x-show="$store.confirm.open"
     @keydown.escape.window="$store.confirm.close()"
     class="fixed inset-0 z-50 flex items-center justify-center px-4"
     role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div x-show="$store.confirm.open" x-transition.opacity.duration.150ms
         @click="$store.confirm.close()"
         class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

    {{-- Dialog --}}
    <div x-show="$store.confirm.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-md rounded-2xl border border-bp-border bg-bp-surface shadow-2xl overflow-hidden">

        {{-- Top accent --}}
        <div class="absolute inset-x-0 top-0 h-px"
             :class="$store.confirm.danger
                ? 'bg-gradient-to-r from-transparent via-red-500/70 to-transparent'
                : 'bg-gradient-to-r from-transparent via-bp-primary/60 to-transparent'"></div>

        <div class="p-6">
            <div class="flex items-start gap-4">
                {{-- Icon --}}
                <div class="flex-shrink-0 h-11 w-11 rounded-xl flex items-center justify-center"
                     :class="$store.confirm.danger
                        ? 'bg-red-500/10 text-red-400 ring-1 ring-red-500/30'
                        : 'bg-bp-primary/10 text-bp-primary ring-1 ring-bp-primary/30'">
                    <svg x-show="$store.confirm.danger" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z"/>
                    </svg>
                    <svg x-show="!$store.confirm.danger" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                {{-- Text --}}
                <div class="flex-1 min-w-0 pt-1">
                    <h3 class="font-display text-lg font-semibold text-bp-gray-900 leading-tight"
                        x-text="$store.confirm.title"></h3>
                    <p class="mt-1.5 text-sm text-bp-muted leading-relaxed"
                       x-show="$store.confirm.message"
                       x-text="$store.confirm.message"></p>
                </div>

                {{-- Close X --}}
                <button type="button"
                        @click="$store.confirm.close()"
                        class="flex-shrink-0 p-1 rounded-lg text-bp-muted hover:text-bp-gray-800 hover:bg-white/5 transition-colors"
                        aria-label="{{ __('bpadmin::common.modals.close') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-2 mt-6">
                <button type="button"
                        @click="$store.confirm.close()"
                        class="bp-btn-ghost inline-flex items-center text-sm font-medium py-2 px-4 rounded-xl"
                        x-text="$store.confirm.cancelText"></button>
                <button type="button"
                        x-ref="confirmBtn"
                        x-init="$watch('$store.confirm.open', v => v && $nextTick(() => $refs.confirmBtn.focus()))"
                        @click="$store.confirm.confirm()"
                        class="inline-flex items-center gap-2 text-sm font-semibold py-2 px-4 rounded-xl"
                        :class="$store.confirm.danger ? 'bp-btn-danger' : 'bp-btn-primary'">
                    <svg x-show="$store.confirm.danger" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span x-text="$store.confirm.confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
