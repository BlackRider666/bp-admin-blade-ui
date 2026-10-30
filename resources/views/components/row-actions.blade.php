{{-- Row "..." dropdown (A40). Menu is teleported to <body> and positioned from the trigger rect so the table's overflow cannot clip it. --}}
@php $rowActionBase = 'bpra-' . $definition->name() . '-' . $rowId; @endphp
<div data-bp-row-actions
     x-data="{ open: false, top: 0, right: 0,
               toggle(el) { const r = el.getBoundingClientRect(); this.top = r.bottom + 4; this.right = window.innerWidth - r.right; this.open = !this.open } }"
     @keydown.escape.window="open = false"
     @scroll.window="open = false"
     @bp-action-open.window="open = false"
     class="relative">
    <button type="button"
            x-ref="trigger"
            @click="toggle($el)"
            aria-haspopup="menu"
            :aria-expanded="open"
            aria-label="{{ __('bpadmin::common.row_actions.label') }}"
            class="p-2 rounded-lg text-bp-muted hover:text-bp-gray-900 hover:bg-white/5 transition-colors">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
    </button>
    <template x-teleport="body">
        <div x-show="open" x-cloak
             @click.outside="if (!$refs.trigger.contains($event.target)) open = false"
             role="menu"
             :style="`top:${top}px;right:${right}px`"
             class="fixed z-40 min-w-[11rem] rounded-xl border border-bp-border bg-bp-surface shadow-2xl py-1 text-left">
            @foreach($ui->rowActions as $rowAction)
                @php $rowActionUrl = route('bpadmin.entity.action.row', ['entity' => $definition->name(), 'id' => $rowId, 'action' => $rowAction->name]); @endphp
                @include('bpadmin::components.action-button', ['action' => $rowAction, 'scope' => 'row', 'rowId' => $rowId, 'url' => $rowActionUrl, 'variant' => 'menu'])
            @endforeach
        </div>
    </template>
    @foreach($ui->rowActions as $rowAction)
        @if($rowAction->hasForm)
            @include('bpadmin::components.action-modal', ['action' => $rowAction, 'scope' => 'row', 'rowId' => $rowId, 'url' => route('bpadmin.entity.action.row', ['entity' => $definition->name(), 'id' => $rowId, 'action' => $rowAction->name])])
        @endif
    @endforeach
</div>
