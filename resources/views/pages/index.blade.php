@extends('bpadmin::layout.main')

@section('breadcrumbs')
    <a href="{{ route('bpadmin.dashboard') }}" class="flex items-center gap-1.5 hover:text-bp-gray-700 transition-colors">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Dashboard
    </a>
    <svg class="mx-1 h-3 w-3 text-bp-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-bp-gray-800 font-medium">{{ bp_entity_label($definition) }}</span>
@endsection

@section('content')
<div class="bp-enter">
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="font-display text-2xl md:text-3xl font-bold tracking-tight text-bp-gray-900">{{ bp_entity_label($definition) }}</h1>
            <p class="text-sm text-bp-muted mt-1">
                Browse, search and manage
                <span class="font-mono-bp text-bp-gray-700">{{ $definition->name() }}</span>
                records.
            </p>
        </div>
        <a href="{{ route('bpadmin.entity.create', ['entity' => $definition->name()]) }}"
           data-bp-new
           title="Press N to create"
           class="bp-btn-primary inline-flex items-center gap-2 text-sm font-semibold py-2.5 px-4 rounded-xl group">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>{{ __('bpadmin::common.buttons.new', ['entity' => \Illuminate\Support\Str::singular(bp_entity_label($definition))]) }}</span>
            <kbd class="hidden sm:inline-flex items-center rounded-md border border-white/25 bg-white/10 px-1.5 py-0.5 text-[10px] font-mono-bp">N</kbd>
        </a>
    </div>

    @include('bpadmin::components.table', [
        'fields'           => $fields,
        'records'          => $records,
        'paginated'        => $paginated,
        'definition'       => $definition,
        'searchable'       => count($definition->searchFields()) > 0,
        'currentQ'         => request('q', ''),
        'currentSort'      => request('sort_by', ''),
        'currentDir'       => request('sort_dir', 'asc'),
        'filterableFields' => array_values(array_filter($fields, fn ($f) => $f->isFilterable())),
        'currentFilters'   => request('filter', []),
    ])
</div>
@endsection
