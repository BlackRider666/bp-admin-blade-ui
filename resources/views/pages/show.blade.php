@extends('bpadmin::layout.main')

@section('breadcrumbs')
    <a href="{{ route('bpadmin.dashboard') }}" class="hover:text-bp-gray-700 transition-colors">Dashboard</a>
    <svg class="mx-1 h-3 w-3 text-bp-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('bpadmin.entity.index', ['entity' => $definition->name()]) }}" class="hover:text-bp-gray-700 transition-colors">{{ bp_entity_label($definition) }}</a>
    <svg class="mx-1 h-3 w-3 text-bp-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="font-mono-bp text-xs text-bp-gray-800 font-medium">#{{ $record->id() }}</span>
@endsection

@section('content')
<div class="max-w-3xl bp-enter">
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-bp-primary font-semibold mb-2 flex items-center gap-2">
                {{ bp_entity_label($definition) }}
                <span class="text-bp-muted/60">·</span>
                <button type="button"
                        x-data="bpCopy({{ \Illuminate\Support\Js::from((string) $record->id()) }})"
                        @click="copy()"
                        title="Click to copy ID"
                        class="font-mono-bp normal-case tracking-normal inline-flex items-center gap-1 hover:text-bp-gray-900 transition-colors group">
                    #{{ $record->id() }}
                    <svg x-show="!copied" class="h-3 w-3 opacity-0 group-hover:opacity-60 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <svg x-show="copied" x-cloak class="h-3 w-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </p>
            <h1 class="font-display text-2xl md:text-3xl font-bold tracking-tight text-bp-gray-900">
                {{ \Illuminate\Support\Str::singular(bp_entity_label($definition)) }} details
            </h1>
            <p class="text-sm text-bp-muted mt-1">View all fields for this record.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('bpadmin.entity.edit', ['entity' => $definition->name(), 'id' => $record->id()]) }}"
               class="bp-btn-ghost inline-flex items-center gap-2 text-sm font-medium py-2 px-4 rounded-xl">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <form method="POST"
                  action="{{ route('bpadmin.entity.destroy', ['entity' => $definition->name(), 'id' => $record->id()]) }}">
                @csrf
                @method('DELETE')
                <button type="button"
                        @click="$store.confirm.ask({
                            title: 'Delete {{ \Illuminate\Support\Str::singular(bp_entity_label($definition)) }} #{{ $record->id() }}?',
                            message: 'This record will be permanently removed. This action cannot be undone.',
                            confirmText: 'Delete',
                            danger: true,
                            onConfirm: () => $el.closest('form').submit(),
                        })"
                        class="bp-btn-danger inline-flex items-center gap-2 text-sm font-medium py-2 px-4 rounded-xl">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-bp-border bg-bp-surface shadow-card">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-bp-primary/60 to-transparent"></div>
        <div class="divide-y divide-bp-border/70">
            @forelse($fields as $field)
                <div class="flex flex-col md:flex-row gap-2 md:gap-6 px-6 py-4 hover:bg-white/[0.02] transition-colors">
                    <div class="md:w-48 flex-shrink-0">
                        <p class="text-[10px] uppercase tracking-widest text-bp-muted font-semibold">{{ bp_field_label($definition, $field) }}</p>
                    </div>
                    <div class="flex-1 text-sm text-bp-gray-800">
                        @include('bpadmin::components.field-display', ['field' => $field, 'record' => $record, 'definition' => $definition])
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-bp-muted text-sm">No fields to display.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('bpadmin.entity.index', ['entity' => $definition->name()]) }}"
           class="inline-flex items-center gap-1.5 text-sm text-bp-muted hover:text-bp-gray-800 transition-colors group">
            <svg class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Back to {{ bp_entity_label($definition) }}
        </a>
    </div>
</div>
@endsection
