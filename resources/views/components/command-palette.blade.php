@php
    $registry = app(\BlackParadise\LaravelAdmin\Core\EntityDefinitionRegistry::class);
    $entries = [];
    $entries[] = [
        'label' => 'Dashboard',
        'name'  => 'dashboard',
        'url'   => route('bpadmin.dashboard'),
        'kind'  => 'page',
    ];
    foreach ($registry->all() as $def) {
        $entries[] = [
            'label' => $def->label(),
            'name'  => $def->name(),
            'url'   => route('bpadmin.entity.index', ['entity' => $def->name()]),
            'kind'  => 'entity',
        ];
        $entries[] = [
            'label' => 'New ' . \Illuminate\Support\Str::singular($def->label()),
            'name'  => 'new ' . $def->name(),
            'url'   => route('bpadmin.entity.create', ['entity' => $def->name()]),
            'kind'  => 'action',
        ];
    }
@endphp

<div x-show="$store.ui.paletteOpen" x-cloak
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 bp-palette-backdrop flex items-start justify-center pt-[12vh] px-4"
     @click.self="$store.ui.closePalette()">
    <div class="bp-palette w-full max-w-xl rounded-2xl overflow-hidden"
         x-data="bpPalette()"
         data-entities='@json($entries)'
         @keydown.down.prevent="move(1)"
         @keydown.up.prevent="move(-1)"
         @keydown.enter.prevent="activate()">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-bp-border-soft">
            <svg class="h-4 w-4 text-bp-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text"
                   x-ref="input"
                   x-model="query"
                   @input="index = 0"
                   placeholder="Jump to entity, action…"
                   class="flex-1 bg-transparent outline-none text-base text-bp-gray-900 placeholder-bp-muted"
                   style="border:none; box-shadow:none;">
            <kbd class="text-[10px] text-bp-muted border border-bp-border-soft rounded px-1.5 py-0.5">ESC</kbd>
        </div>
        <div class="max-h-[50vh] overflow-y-auto py-1">
            <template x-for="(hit, i) in results" :key="hit.name">
                <a :href="hit.url"
                   @mouseenter="index = i"
                   class="flex items-center gap-3 px-5 py-2.5 text-sm transition-colors"
                   :class="index === i ? 'bg-bp-primary/10 text-bp-gray-900' : 'text-bp-gray-700 hover:bg-white/5'">
                    <span class="h-7 w-7 rounded-lg flex items-center justify-center flex-shrink-0"
                          :class="hit.kind === 'entity' ? 'bg-bp-primary/15 text-bp-primary'
                                  : hit.kind === 'action' ? 'bg-emerald-400/15 text-emerald-300'
                                  : 'bg-white/5 text-bp-muted'">
                        <template x-if="hit.kind === 'entity'">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </template>
                        <template x-if="hit.kind === 'action'">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                        </template>
                        <template x-if="hit.kind === 'page'">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3"/></svg>
                        </template>
                    </span>
                    <span class="flex-1" x-text="hit.label"></span>
                    <span class="text-[10px] uppercase tracking-widest text-bp-muted" x-text="hit.kind"></span>
                </a>
            </template>
            <div x-show="results.length === 0" class="px-5 py-6 text-center text-sm text-bp-muted">
                No matches
            </div>
        </div>
        <div class="flex items-center gap-4 px-5 py-2 border-t border-bp-border-soft text-[10px] uppercase tracking-widest text-bp-muted">
            <span><kbd class="border border-bp-border rounded px-1">↑↓</kbd> navigate</span>
            <span><kbd class="border border-bp-border rounded px-1">↵</kbd> open</span>
            <span class="ml-auto"><kbd class="border border-bp-border rounded px-1">⌘K</kbd> toggle</span>
        </div>
    </div>
</div>
