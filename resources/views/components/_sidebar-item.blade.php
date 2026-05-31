@php
    $isActive = request()->routeIs('bpadmin.entity.*')
                && request()->route('entity') === $def->name();
@endphp
<a href="{{ route('bpadmin.entity.index', ['entity' => $def->name()]) }}"
   class="bp-nav-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium bp-sidebar-center {{ $isActive ? 'bp-nav-item-active' : '' }}"
   :title="$store.ui.sidebarCollapsed ? '{{ bp_entity_label($def) }}' : ''">
    <svg class="bp-nav-icon h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 7h16M4 12h10M4 17h13"/>
    </svg>
    <span class="flex-1 truncate bp-sidebar-expand">{{ bp_entity_label($def) }}</span>
    @if($isActive)
        <span class="h-1.5 w-1.5 rounded-full bg-bp-primary shadow-[0_0_8px_rgba(48,164,136,0.9)] bp-sidebar-expand"></span>
    @endif
</a>
