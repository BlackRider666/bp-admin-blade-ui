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
@endphp

@section('content')
<div class="bp-enter">
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-3xl bg-bp-surface border border-bp-border p-8 mb-8">
        <div class="absolute inset-0 bg-aura-primary opacity-80 pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-bp-primary/20 blur-3xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-grid-dots bg-grid-24 opacity-40 pointer-events-none"></div>

        <div class="relative">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-bp-primary font-semibold mb-3">Overview</p>
                <h1 class="font-display text-3xl md:text-4xl font-bold tracking-tight text-bp-gray-900 leading-tight">
                    {{ $greeting }},
                    <span class="bp-gradient-text">{{ $user?->name ?? 'Admin' }}</span>
                </h1>
                <p class="mt-2 text-sm text-bp-muted max-w-lg">
                    Welcome back to {{ config('bpadmin.name', 'BPAdmin') }}. Use the sidebar to manage your entities.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
