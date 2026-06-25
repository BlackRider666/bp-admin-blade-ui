@props([
    'fields', 'records', 'paginated', 'definition',
    'searchable'       => false,
    'currentQ'         => '',
    'currentSort'      => '',
    'currentDir'       => 'asc',
    'filterableFields' => [],
    'currentFilters'   => [],
])

@php
    $indexRoute = route('bpadmin.entity.index', ['entity' => $definition->name()]);
    $perPage    = (int) request('per_page', $definition->defaultPerPage());
    $perPageOptions = [10, 15, 25, 50, 100];
    $activeFilterCount = count(array_filter((array)$currentFilters));
    $selectedId = 'bp-sel-' . $definition->name();
@endphp

{{-- Toolbar --}}
@if($searchable || count($filterableFields) > 0)
<div class="mb-4 flex flex-wrap items-start gap-3"
     x-data="{ filtersOpen: {{ $activeFilterCount > 0 ? 'true' : 'false' }} }"
     @keydown.window.slash.prevent.stop="$refs.search && $refs.search.focus()">
    @if($searchable)
    <form method="GET" action="{{ $indexRoute }}" class="flex gap-2 flex-1 min-w-[260px] max-w-md">
        <input type="hidden" name="sort_by"  value="{{ $currentSort }}">
        <input type="hidden" name="sort_dir" value="{{ $currentDir }}">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        @foreach(array_filter((array)$currentFilters) as $fKey => $fVal)
            <input type="hidden" name="filter[{{ $fKey }}]" value="{{ $fVal }}">
        @endforeach
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-3 flex items-center text-bp-muted pointer-events-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </span>
            <input type="text"
                   name="q"
                   x-ref="search"
                   value="{{ $currentQ }}"
                   placeholder="{{ __('bpadmin::common.table.search_placeholder') }} ( / )"
                   class="block w-full rounded-xl border pl-10 pr-16 py-2 text-sm">
            <kbd class="absolute inset-y-0 right-3 my-auto h-5 flex items-center rounded-md border border-bp-border bg-bp-surface-2 px-1.5 text-[10px] font-mono-bp text-bp-muted pointer-events-none {{ $currentQ ? 'opacity-0' : '' }}">/</kbd>
            @if($currentQ)
                <a href="{{ $indexRoute }}" title="Clear search"
                   class="absolute inset-y-0 right-3 flex items-center text-bp-muted hover:text-bp-gray-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endif
        </div>
    </form>
    @endif

    @if(count($filterableFields) > 0)
    <form method="GET" action="{{ $indexRoute }}" class="flex flex-wrap gap-2 items-end">
        <input type="hidden" name="sort_by"  value="{{ $currentSort }}">
        <input type="hidden" name="sort_dir" value="{{ $currentDir }}">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        @if($currentQ)
            <input type="hidden" name="q" value="{{ $currentQ }}">
        @endif

        <button type="button" @click="filtersOpen = !filtersOpen"
                class="bp-btn-ghost inline-flex items-center gap-2 px-3 py-2 text-sm rounded-xl">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filters
            @if($activeFilterCount > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-[10px] font-bold text-white bg-bp-primary rounded-full">{{ $activeFilterCount }}</span>
            @endif
        </button>

        <div x-show="filtersOpen" x-collapse x-transition class="flex flex-wrap gap-2 items-end">
            @foreach($filterableFields as $filter)
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] uppercase tracking-wider text-bp-muted font-semibold">{{ $filter->label() }}</label>
                    <input type="text"
                           name="filter[{{ $filter->name() }}]"
                           value="{{ $currentFilters[$filter->name()] ?? '' }}"
                           placeholder="{{ $filter->label() }}"
                           class="rounded-xl border px-3 py-2 text-sm w-40">
                </div>
            @endforeach
            <button type="submit"
                    class="bp-btn-primary px-4 py-2 text-sm font-semibold rounded-xl">
                Apply
            </button>
            @if($activeFilterCount > 0)
                <a href="{{ $indexRoute }}?{{ http_build_query(array_filter(['sort_by' => $currentSort ?: null, 'sort_dir' => $currentDir !== 'asc' ? $currentDir : null, 'q' => $currentQ ?: null, 'per_page' => $perPage !== $definition->defaultPerPage() ? $perPage : null])) }}"
                   class="bp-btn-ghost inline-flex items-center px-3 py-2 text-sm rounded-xl">
                    Clear
                </a>
            @endif
        </div>
    </form>
    @endif

    {{-- Per-page selector --}}
    <div class="ml-auto flex items-center gap-2 text-xs text-bp-muted">
        <label for="{{ $selectedId }}" class="uppercase tracking-wider font-semibold">{{ __('bpadmin::common.table.rows_per_page') }}</label>
        <select id="{{ $selectedId }}"
                onchange="window.location.href = this.value"
                class="rounded-lg border py-1.5 pl-2.5 pr-7 text-sm">
            @foreach($perPageOptions as $opt)
                <option value="{{ request()->fullUrlWithQuery(['per_page' => $opt, 'page' => 1]) }}"
                        {{ $opt === $perPage ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
    </div>
</div>
@endif

{{-- Bulk action bar --}}
<div x-data="bpBulkSelect()"
     x-ref="bulk"
     class="relative">

    <div x-show="selected.length > 0" x-cloak x-transition
         class="sticky top-0 z-10 mb-3 flex items-center gap-3 rounded-xl border border-bp-primary/40 bg-bp-primary/10 px-4 py-2.5 text-sm backdrop-blur">
        <span class="inline-flex items-center gap-1.5 text-bp-primary font-semibold">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span x-text="selected.length"></span> selected
        </span>
        <button @click="clear()" class="text-xs uppercase tracking-wider text-bp-muted hover:text-bp-gray-800 transition-colors">Clear</button>
        <div class="ml-auto flex items-center gap-2">
            <form method="POST"
                  x-ref="bulkForm"
                  action="{{ route('bpadmin.entity.bulk-destroy', ['entity' => $definition->name()]) }}">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="button"
                        @click="$store.confirm.ask({
                            title: 'Delete ' + selected.length + ' ' + (selected.length === 1 ? 'record' : 'records') + '?',
                            message: 'This action cannot be undone.',
                            confirmText: 'Delete',
                            danger: true,
                            onConfirm: () => $refs.bulkForm.submit(),
                        })"
                        class="bp-btn-danger inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-lg">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6"/></svg>
                    Delete <span x-text="selected.length"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="relative overflow-hidden rounded-2xl border border-bp-border bg-bp-surface shadow-card">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-bp-primary/40 to-transparent pointer-events-none"></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-bp-border bg-bp-surface-2/40">
                        <th class="w-10 pl-5 pr-2 py-3.5">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       class="bp-checkbox"
                                       @change="toggleAll($event.target.checked)"
                                       :checked="allSelected">
                            </label>
                        </th>
                        @foreach($fields as $field)
                            <th class="px-5 py-3.5 text-left text-[11px] font-bold text-bp-muted uppercase tracking-[0.12em] whitespace-nowrap">
                                @if($field->isSortable())
                                    @php
                                        $isSorted = $currentSort === $field->name();
                                        $nextDir  = ($isSorted && $currentDir === 'asc') ? 'desc' : 'asc';
                                        $sortParams = array_filter([
                                            'sort_by'  => $field->name(),
                                            'sort_dir' => $nextDir,
                                            'q'        => $currentQ ?: null,
                                            'per_page' => $perPage !== $definition->defaultPerPage() ? $perPage : null,
                                        ]);
                                        $activeFilters = array_filter((array)$currentFilters);
                                        if (!empty($activeFilters)) {
                                            $sortParams['filter'] = $activeFilters;
                                        }
                                        $sortUrl  = $indexRoute . '?' . http_build_query($sortParams);
                                    @endphp
                                    <a href="{{ $sortUrl }}" class="inline-flex items-center gap-1.5 hover:text-bp-gray-800 group transition-colors {{ $isSorted ? 'text-bp-primary' : '' }}">
                                        {{ bp_field_label($definition, $field) }}
                                        @if($isSorted)
                                            <svg class="h-3 w-3 {{ $currentDir === 'desc' ? 'rotate-180' : '' }} transition-transform" fill="currentColor" viewBox="0 0 10 6">
                                                <path d="M5 0l5 6H0z"/>
                                            </svg>
                                        @else
                                            <svg class="h-3 w-3 opacity-30 group-hover:opacity-60 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                            </svg>
                                        @endif
                                    </a>
                                @else
                                    {{ bp_field_label($definition, $field) }}
                                @endif
                            </th>
                        @endforeach
                        <th class="px-5 py-3.5 text-right text-[11px] font-bold text-bp-muted uppercase tracking-[0.12em]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php $rid = (string) $record->id(); @endphp
                        <tr class="border-b border-bp-border/40 last:border-0 transition-colors"
                            :class="selected.includes('{{ $rid }}') ? 'bg-bp-primary/5' : ''">
                            <td class="w-10 pl-5 pr-2 py-4">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           class="bp-checkbox"
                                           value="{{ $rid }}"
                                           @change="toggle('{{ $rid }}', $event.target.checked)"
                                           :checked="selected.includes('{{ $rid }}')">
                                </label>
                            </td>
                            @foreach($fields as $field)
                                <td class="px-5 py-4 text-bp-gray-700 align-middle">
                                    @include('bpadmin::components.field-display', ['field' => $field, 'record' => $record])
                                </td>
                            @endforeach
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('bpadmin.entity.show', ['entity' => $definition->name(), 'id' => $rid]) }}"
                                       class="p-2 rounded-lg text-bp-muted hover:text-bp-gray-900 hover:bg-white/5 transition-colors" title="View">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><use href="#bp-i-view"/></svg>
                                    </a>
                                    <a href="{{ route('bpadmin.entity.edit', ['entity' => $definition->name(), 'id' => $rid]) }}"
                                       class="p-2 rounded-lg text-bp-primary/70 hover:text-bp-primary hover:bg-bp-primary/10 transition-colors" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><use href="#bp-i-edit"/></svg>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('bpadmin.entity.destroy', ['entity' => $definition->name(), 'id' => $rid]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                @click="$store.confirm.ask({
                                                    title: 'Delete this record?',
                                                    message: 'Record #{{ $rid }} will be permanently removed. This action cannot be undone.',
                                                    confirmText: 'Delete',
                                                    danger: true,
                                                    onConfirm: () => $el.closest('form').submit(),
                                                })"
                                                class="p-2 rounded-lg text-red-400/80 hover:text-red-300 hover:bg-red-500/10 transition-colors" title="Delete">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><use href="#bp-i-trash"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($fields) + 2 }}" class="px-5 py-20 text-center">
                                <div class="flex flex-col items-center gap-4 text-bp-muted">
                                    <div class="relative h-16 w-16">
                                        <div class="absolute inset-0 rounded-2xl bg-bp-primary/10 blur-xl"></div>
                                        <div class="relative h-16 w-16 rounded-2xl bg-bp-surface-2 border border-bp-border flex items-center justify-center">
                                            <svg class="h-6 w-6 text-bp-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-5l-1.5 2h-3L9 13H4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm text-bp-gray-700 font-medium">
                                            @if($currentQ || $activeFilterCount > 0)
                                                No matches
                                            @else
                                                Nothing here yet
                                            @endif
                                        </p>
                                        <p class="text-xs text-bp-muted mt-1">
                                            @if($currentQ || $activeFilterCount > 0)
                                                Try different keywords or clear filters.
                                            @else
                                                {{ __('bpadmin::common.table.empty_cta', ['entity' => \Illuminate\Support\Str::singular(strtolower(bp_entity_label($definition)))]) }}
                                            @endif
                                        </p>
                                    </div>
                                    @if(!$currentQ && $activeFilterCount === 0)
                                        <a href="{{ route('bpadmin.entity.create', ['entity' => $definition->name()]) }}"
                                           class="bp-btn-primary inline-flex items-center gap-2 text-sm font-semibold py-2 px-4 rounded-xl mt-1">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                                            {{ __('bpadmin::common.table.create_button', ['entity' => \Illuminate\Support\Str::singular(bp_entity_label($definition))]) }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('bpadmin::components.pagination', [
    'paginated'      => $paginated,
    'currentQ'       => $currentQ,
    'currentSort'    => $currentSort,
    'currentDir'     => $currentDir,
    'currentFilters' => $currentFilters,
])
