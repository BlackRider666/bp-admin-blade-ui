@extends('bpadmin::layout.main')

@section('breadcrumbs')
    <span class="flex items-center gap-1.5 text-bp-gray-800 font-medium">
        <svg class="h-3.5 w-3.5 text-bp-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Dashboard
    </span>
@endsection

@php
    $user = auth()->user();
    $greetingHour = (int) now()->format('H');
    $greeting = $greetingHour < 5  ? 'Good night'
              : ($greetingHour < 12 ? 'Good morning'
              : ($greetingHour < 18 ? 'Good afternoon' : 'Good evening'));
    $entitiesTotal = count($entities ?? []);
@endphp

@section('content')
<div class="bp-enter">
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-3xl bg-bp-surface border border-bp-border p-8 mb-8">
        <div class="absolute inset-0 bg-aura-primary opacity-80 pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-bp-primary/20 blur-3xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-grid-dots bg-grid-24 opacity-40 pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-bp-primary font-semibold mb-3">Overview</p>
                <h1 class="font-display text-3xl md:text-4xl font-bold tracking-tight text-bp-gray-900 leading-tight">
                    {{ $greeting }},
                    <span class="bp-gradient-text">{{ $user?->name ?? 'Admin' }}</span>
                </h1>
                <p class="mt-2 text-sm text-bp-muted max-w-lg">
                    Welcome back to {{ config('bpadmin.name', 'BPAdmin') }}. Manage your entities, review activity and keep things running smoothly.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-3 md:gap-4 min-w-[320px]">
                <div class="rounded-2xl border border-bp-border-soft bg-bp-surface-2/70 px-4 py-3">
                    <p class="text-[10px] uppercase tracking-widest text-bp-muted">Entities</p>
                    <p class="font-display text-2xl font-bold text-bp-gray-900 mt-0.5">{{ $entitiesTotal }}</p>
                </div>
                <div class="rounded-2xl border border-bp-border-soft bg-bp-surface-2/70 px-4 py-3">
                    <p class="text-[10px] uppercase tracking-widest text-bp-muted">Status</p>
                    <p class="font-display text-sm font-semibold text-emerald-300 mt-1.5 flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.9)]"></span>
                        Online
                    </p>
                </div>
                <div class="rounded-2xl border border-bp-border-soft bg-bp-surface-2/70 px-4 py-3">
                    <p class="text-[10px] uppercase tracking-widest text-bp-muted">Today</p>
                    <p class="font-mono-bp text-sm font-semibold text-bp-gray-800 mt-1.5">{{ now()->format('M d') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Entities grid --}}
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg font-semibold text-bp-gray-900">Entities</h2>
            <span class="text-xs text-bp-muted">{{ $entitiesTotal }} {{ \Illuminate\Support\Str::plural('item', $entitiesTotal) }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($entities as $entity)
                <a href="{{ route('bpadmin.entity.index', ['entity' => $entity['name']]) }}"
                   class="group relative overflow-hidden rounded-2xl bg-bp-surface border border-bp-border p-5 transition-all duration-300 hover:border-bp-primary/50 hover:-translate-y-0.5 shadow-card hover:shadow-card-hover">
                    <div class="absolute -top-12 -right-10 h-32 w-32 rounded-full bg-bp-primary/10 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                    <div class="relative flex items-start justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-bp-primary/10 border border-bp-primary/20 flex items-center justify-center text-bp-primary group-hover:bg-bp-primary/20 group-hover:border-bp-primary/40 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6h16M4 10h16M4 14h10M4 18h7"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-mono-bp uppercase tracking-wider text-bp-muted group-hover:text-bp-primary transition-colors">{{ $entity['name'] }}</span>
                    </div>
                    <div class="relative">
                        <p class="font-display text-base font-semibold text-bp-gray-900 group-hover:text-bp-primary transition-colors">
                            {{ $entity['label'] }}
                        </p>
                        <p class="text-xs text-bp-muted mt-1">{{ __('bpadmin::common.dashboard.manage', ['entity' => strtolower($entity['label'])]) }}</p>
                    </div>

                    <div class="relative mt-5 flex items-center text-xs font-semibold text-bp-muted group-hover:text-bp-primary transition-colors">
                        <span>Open</span>
                        <svg class="ml-1 h-3 w-3 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-bp-border-soft bg-bp-surface/50 p-10 text-center">
                    <div class="h-12 w-12 mx-auto rounded-2xl bg-bp-surface-2 border border-bp-border flex items-center justify-center text-bp-muted">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <p class="mt-4 font-display text-base font-semibold text-bp-gray-800">No entities yet</p>
                    <p class="mt-1 text-sm text-bp-muted">Register an <code class="font-mono-bp text-bp-gray-700">EntityDefinition</code> to see it here.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
