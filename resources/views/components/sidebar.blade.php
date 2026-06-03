@php
    $appName  = config('bpadmin.name', 'BPAdmin');
    $logo     = config('bpadmin.logo');
    $registry = app(\BlackParadise\LaravelAdmin\Core\EntityDefinitionRegistry::class);

    // The sidebar menu is driven purely by config('bpadmin.menu'): an ordered
    // list of groups — each ['label' => ..., 'icon' => ..., 'entities' => [...]].
    // Only the listed entities are rendered, in the exact order and grouping
    // declared in the config (no auto-discovery dump). Entity names that are
    // not registered are silently skipped. The legacy 'nav_groups' key is
    // accepted as a fallback alias for backwards compatibility.
    //
    // Each 'entities' entry is either a bare entity name (inherits the group
    // icon, label from the entity definition) or an array
    // ['entity' => ..., 'icon' => ..., 'label' => ...] to override that single
    // item's icon and/or label. Group and item labels are localized via
    // bp_menu_label() — a literal string or a translation key both work.
    $menu = config('bpadmin.menu', config('bpadmin.nav_groups', []));

    $groupedEntities   = [];
    $ungroupedEntities = [];

    if (!empty($menu)) {
        foreach ($menu as $group) {
            $groupIcon = $group['icon'] ?? null;
            $groupDefs = [];
            foreach (($group['entities'] ?? []) as $entry) {
                $entityName = is_array($entry) ? ($entry['entity'] ?? null) : $entry;
                $itemIcon   = is_array($entry) ? ($entry['icon'] ?? $groupIcon) : $groupIcon;
                $itemLabel  = is_array($entry) ? ($entry['label'] ?? null) : null;

                if ($entityName !== null && $registry->has($entityName)) {
                    $groupDefs[] = [
                        'def'   => $registry->get($entityName),
                        'icon'  => $itemIcon,
                        'label' => $itemLabel,
                    ];
                }
            }
            if (!empty($groupDefs)) {
                $groupedEntities[] = [
                    'label' => $group['label'] ?? '',
                    'icon'  => $groupIcon,
                    'defs'  => $groupDefs,
                ];
            }
        }
    } else {
        // Zero-config fallback: when no menu is configured, list every
        // registered entity (ungrouped) so a fresh install is not empty.
        $ungroupedEntities = array_values($registry->all());
    }
@endphp

<aside class="bg-bp-surface border-r border-bp-border flex flex-col flex-shrink-0 relative transition-[width] duration-200"
       :class="$store.ui.sidebarCollapsed ? 'bp-sidebar-collapsed w-[76px]' : 'w-64'">
    <div class="absolute inset-0 pointer-events-none opacity-40 bg-aura-primary"></div>

    {{-- Brand area --}}
    <div class="relative flex h-[82px] items-center px-5 border-b border-bp-border flex-shrink-0 bp-sidebar-center">
        <div class="relative h-10 w-10 rounded-xl bg-primary-sheen flex items-center justify-center flex-shrink-0 overflow-hidden shadow-glow-primary"
             :class="$store.ui.sidebarCollapsed ? '' : 'mr-3'">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $appName }}" class="h-full w-full object-cover">
            @else
                <span class="text-white text-xs font-display font-bold tracking-wide select-none">{{ mb_strtoupper(mb_substr($appName, 0, 3)) }}</span>
            @endif
        </div>
        <div class="flex flex-col min-w-0 bp-sidebar-expand">
            <span class="font-display text-base font-bold text-bp-gray-900 leading-tight truncate">{{ $appName }}</span>
            <span class="text-[10px] uppercase tracking-widest text-bp-muted font-medium">Control panel</span>
        </div>
    </div>

    <nav class="relative flex-1 overflow-y-auto overflow-x-hidden py-4 px-2">
        {{-- Dashboard --}}
        @php $isDash = request()->routeIs('bpadmin.dashboard'); @endphp
        <a href="{{ route('bpadmin.dashboard') }}"
           class="bp-nav-item flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl mb-1 bp-sidebar-center {{ $isDash ? 'bp-nav-item-active' : '' }}"
           :title="$store.ui.sidebarCollapsed ? 'Dashboard' : ''">
            {!! bp_icon('dashboard', 'bp-nav-icon h-4 w-4 flex-shrink-0') !!}
            <span class="flex-1 bp-sidebar-expand">Dashboard</span>
            @if($isDash)
                <span class="h-1.5 w-1.5 rounded-full bg-bp-primary shadow-[0_0_8px_rgba(48,164,136,0.9)] bp-sidebar-expand"></span>
            @endif
        </a>

        {{-- Grouped entities --}}
        @foreach($groupedEntities as $group)
            <div x-data="{ open: true }" class="mt-5">
                <button @click="open = !open"
                        class="flex w-full items-center justify-between px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-bp-muted hover:text-bp-gray-600 transition-colors bp-sidebar-expand">
                    <span class="flex items-center gap-2 min-w-0">
                        @if(!empty($group['icon'])){!! bp_icon($group['icon'], 'h-3.5 w-3.5 flex-shrink-0') !!}@endif
                        <span class="truncate">{{ bp_menu_label($group['label']) }}</span>
                    </span>
                    <svg :class="open ? 'rotate-180' : ''" class="h-3 w-3 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open || $store.ui.sidebarCollapsed" x-cloak x-transition class="mt-1 space-y-0.5">
                    @foreach($group['defs'] as $item)
                        @include('bpadmin::components._sidebar-item', ['def' => $item['def'], 'icon' => $item['icon'], 'label' => $item['label']])
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Ungrouped / all entities when no groups defined --}}
        @if(!empty($ungroupedEntities))
            @if(!empty($groupedEntities))
                <p class="px-3 pt-5 pb-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-bp-muted bp-sidebar-expand">Other</p>
            @else
                <p class="px-3 pt-5 pb-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-bp-muted bp-sidebar-expand">Entities</p>
            @endif
            <div class="space-y-0.5">
                @foreach($ungroupedEntities as $def)
                    @include('bpadmin::components._sidebar-item', ['def' => $def])
                @endforeach
            </div>
        @endif
    </nav>

    <div class="relative px-4 pb-4 pt-2 flex-shrink-0">
        <div class="bp-hairline mb-3"></div>
        <div class="flex items-center gap-2">
            <button @click="$store.ui.toggleSidebar()"
                    class="bp-btn-ghost h-8 w-8 rounded-lg flex items-center justify-center flex-shrink-0"
                    :title="$store.ui.sidebarCollapsed ? 'Expand sidebar ([)' : 'Collapse sidebar ([)'">
                <svg class="h-3.5 w-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     :class="$store.ui.sidebarCollapsed ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
            <div class="flex items-center gap-2 text-[10px] text-bp-muted uppercase tracking-widest bp-sidebar-expand">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.9)]"></span>
                System online
            </div>
        </div>
    </div>
</aside>
