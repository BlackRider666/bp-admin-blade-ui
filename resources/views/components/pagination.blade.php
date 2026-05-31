@props(['paginated', 'currentQ' => '', 'currentSort' => '', 'currentDir' => 'asc', 'currentFilters' => []])

@php
    $activeFilters = array_filter(
        $currentFilters instanceof \Illuminate\Support\Collection
            ? $currentFilters->all()
            : (array)$currentFilters
    );
    $baseParams  = array_filter([
        'q'        => $currentQ    ?: null,
        'sort_by'  => $currentSort ?: null,
        'sort_dir' => $currentSort ? $currentDir : null,
        'per_page' => request('per_page') ?: null,
    ]);
    if (!empty($activeFilters)) {
        $baseParams['filter'] = $activeFilters;
    }
    $currentPage = $paginated->page;
    $lastPage    = $paginated->lastPage();
    $total       = $paginated->total;
    $perPage     = $paginated->perPage;
    $from        = $total === 0 ? 0 : ($currentPage - 1) * $perPage + 1;
    $to          = min($currentPage * $perPage, $total);

    $pages = array_values(array_filter(
        range(1, $lastPage),
        fn($p) => $p === 1 || $p === $lastPage || abs($p - $currentPage) <= 2
    ));
@endphp

<nav class="flex items-center justify-between mt-5 px-1 flex-wrap gap-3">
    <p class="text-sm text-bp-muted">
        @if($total === 0)
            No records
        @else
            {{ __('bpadmin::common.pagination.showing', ['from' => $from, 'to' => $to, 'total' => $total]) }}
        @endif
    </p>

    @if($paginated->hasPages())
    <div class="flex items-center gap-1">
        @if($currentPage > 1)
            <a href="{{ request()->fullUrlWithQuery(array_merge($baseParams, ['page' => $currentPage - 1])) }}"
               class="px-2.5 py-1.5 text-sm bp-btn-ghost rounded-lg">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        @php $prev = null; @endphp
        @foreach($pages as $pg)
            @if($prev !== null && $pg - $prev > 1)
                <span class="px-1 text-bp-muted text-sm select-none">…</span>
            @endif
            <a href="{{ request()->fullUrlWithQuery(array_merge($baseParams, ['page' => $pg])) }}"
               class="min-w-[34px] h-[34px] inline-flex items-center justify-center px-2 text-sm rounded-lg transition-colors font-mono-bp
                      {{ $pg === $currentPage
                         ? 'bg-bp-primary text-white font-semibold shadow-glow-primary'
                         : 'bp-btn-ghost' }}">
                {{ $pg }}
            </a>
            @php $prev = $pg; @endphp
        @endforeach

        @if($currentPage < $lastPage)
            <a href="{{ request()->fullUrlWithQuery(array_merge($baseParams, ['page' => $currentPage + 1])) }}"
               class="px-2.5 py-1.5 text-sm bp-btn-ghost rounded-lg">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @endif
    </div>
    @endif
</nav>
