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
    <span class="text-bp-gray-800 font-medium">Create</span>
@endsection

@section('content')
<div class="max-w-3xl bp-enter">
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.2em] text-bp-primary font-semibold mb-2">New record</p>
        <h1 class="font-display text-2xl md:text-3xl font-bold tracking-tight text-bp-gray-900">{{ __('bpadmin::common.buttons.create') }} {{ \Illuminate\Support\Str::singular(bp_entity_label($definition)) }}</h1>
        <p class="text-sm text-bp-muted mt-1">Fill in the details below to add a new record.</p>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-bp-border bg-bp-surface shadow-card">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-bp-primary/60 to-transparent"></div>
        <form method="POST"
              x-data="bpForm()"
              action="{{ route('bpadmin.entity.store', ['entity' => $definition->name()]) }}"
              enctype="multipart/form-data"
              class="p-6 md:p-8">
            @csrf

            <div class="space-y-1">
                @foreach($fields as $field)
                    @if($field instanceof \BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractRelationField && $field->isEmbedded())
                        @php
                            $embeddedDefClass = $field->embeddedDefinition();
                            $embeddedDef      = app(\BlackParadise\LaravelAdmin\Core\EntityDefinitionRegistry::class)
                                ->get((new $embeddedDefClass())->resolveName());
                            $embeddedFields   = array_values(array_filter(
                                $embeddedDef->fields(),
                                fn ($f) => $f->visibleOnForm(),
                            ));
                            $embeddedPrefix   = $field->name();
                            $oldValues        = old($embeddedPrefix, []);
                        @endphp
                        <fieldset class="border border-bp-border-soft rounded-xl p-4 mb-4">
                            <legend class="text-sm font-medium text-bp-gray-500 px-2">{{ $field->label() }}</legend>
                            @foreach($embeddedFields as $ef)
                                @include('bpadmin::components.field-input', [
                                    'field'      => $ef,
                                    'value'      => $oldValues[$ef->name()] ?? null,
                                    'errors'     => $errors,
                                    'definition' => $embeddedDef,
                                    'namePrefix' => $embeddedPrefix,
                                ])
                            @endforeach
                        </fieldset>
                    @else
                        @include('bpadmin::components.field-input', [
                            'field'      => $field,
                            'value'      => null,
                            'errors'     => $errors,
                            'definition' => $definition,
                        ])
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3 pt-5 mt-6 border-t border-bp-border">
                <button type="submit"
                        :disabled="submitting"
                        class="bp-btn-primary inline-flex items-center gap-2 text-sm font-semibold py-2.5 px-5 rounded-xl disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg x-show="!submitting" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="submitting" x-cloak class="h-4 w-4 bp-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8"/>
                    </svg>
                    <span x-text="submitting ? 'Creating…' : 'Create'"></span>
                </button>
                <a href="{{ route('bpadmin.entity.index', ['entity' => $definition->name()]) }}"
                   class="bp-btn-ghost inline-flex items-center text-sm font-medium py-2.5 px-5 rounded-xl">Cancel</a>
                <span x-show="dirty && !submitting" x-cloak class="ml-auto text-xs text-amber-400/80 inline-flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    Unsaved changes
                </span>
            </div>
        </form>
    </div>
</div>
@endsection
