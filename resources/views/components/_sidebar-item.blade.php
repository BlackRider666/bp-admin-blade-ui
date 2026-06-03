@php
    $isActive = request()->routeIs('bpadmin.entity.*')
                && request()->route('entity') === $def->name();
    // Items inherit their menu group's icon (so collapsed mode shows a
    // meaningful, per-group glyph rather than one repeated icon). Falls back
    // to the neutral default for the no-menu / ungrouped case.
    $itemIcon = $icon ?? \BlackParadise\LaravelAdminBladeUI\Support\IconSet::DEFAULT;
    // An explicit per-item label (localized) overrides the entity's own label.
    $itemLabel = ($label ?? null) !== null ? bp_menu_label($label) : bp_entity_label($def);
@endphp
<a href="{{ route('bpadmin.entity.index', ['entity' => $def->name()]) }}"
   class="bp-nav-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium bp-sidebar-center {{ $isActive ? 'bp-nav-item-active' : '' }}"
   :title="$store.ui.sidebarCollapsed ? @js($itemLabel) : ''">
    {!! bp_icon($itemIcon, 'bp-nav-icon h-4 w-4 flex-shrink-0') !!}
    <span class="flex-1 truncate bp-sidebar-expand">{{ $itemLabel }}</span>
    @if($isActive)
        <span class="h-1.5 w-1.5 rounded-full bg-bp-primary shadow-[0_0_8px_rgba(48,164,136,0.9)] bp-sidebar-expand"></span>
    @endif
</a>
