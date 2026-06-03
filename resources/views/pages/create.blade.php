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
                            $hostModelClass   = $definition->model ?? null;

                            // Core decorates sub-fields with options (filter + FK-skip already done)
                            // when a RelationOptionsProvider is injected. Use that if available.
                            // Fallback: instantiate the definition ourselves and apply the same
                            // visibleOnForm + FK-to-host filters that core would apply.
                            $embeddedDef    = new $embeddedDefClass();
                            $embeddedFields = $field->meta()['embeddedFields'] ?? null;
                            if ($embeddedFields === null) {
                                $embeddedFields = array_values(array_filter(
                                    $embeddedDef->fields(),
                                    function ($f) use ($hostModelClass) {
                                        if (!$f->visibleOnForm()) {
                                            return false;
                                        }
                                        // skip FK back to host — auto-assigned on save
                                        if ($hostModelClass
                                            && $f instanceof \BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractRelationField
                                            && $f->relationKind() === 'belongsTo'
                                            && $f->target() === $hostModelClass) {
                                            return false;
                                        }
                                        return true;
                                    },
                                ));
                            } else {
                                // FK-skip remains in Blade because only Blade knows $hostModelClass
                                $embeddedFields = array_values(array_filter(
                                    $embeddedFields,
                                    function ($f) use ($hostModelClass) {
                                        if ($hostModelClass
                                            && $f instanceof \BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractRelationField
                                            && $f->relationKind() === 'belongsTo'
                                            && $f->target() === $hostModelClass) {
                                            return false;
                                        }
                                        return true;
                                    },
                                ));
                            }

                            $relationKind   = $field->relationKind();
                            $isMany         = in_array($relationKind, ['hasMany', 'morphMany', 'belongsToMany'], true);
                            $embeddedPrefix = $field->name();
                            $oldItems       = old($embeddedPrefix);
                        @endphp
                        <fieldset class="border border-bp-border-soft rounded-xl p-4 mb-4">
                            <legend class="text-sm font-medium text-bp-gray-500 px-2">{{ $field->label() }}</legend>

                            @if($isMany)
                                @php $items = is_array($oldItems) ? array_values($oldItems) : []; @endphp
                                <div x-data="bpRepeater({{ count($items) }})" data-bp-repeater="{{ $embeddedPrefix }}">
                                    {{-- Rows submitted before a validation error are re-rendered here
                                         so the user does not lose them; new rows are cloned client-side
                                         from the <template> below. --}}
                                    <div x-ref="rows" class="space-y-3">
                                        @foreach($items as $idx => $item)
                                            <div data-bp-embed-row
                                                 class="relative border border-bp-border-soft/60 rounded-lg p-3">
                                                <button type="button"
                                                        @click="$el.closest('[data-bp-embed-row]').remove()"
                                                        class="absolute top-2 right-2 text-bp-muted hover:text-red-400 transition-colors"
                                                        title="Remove">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                                @foreach($embeddedFields as $ef)
                                                    @include('bpadmin::components.field-input', [
                                                        'field'      => $ef,
                                                        'value'      => is_array($item) ? ($item[$ef->name()] ?? null) : null,
                                                        'errors'     => $errors,
                                                        'definition' => $embeddedDef,
                                                        'namePrefix' => $embeddedPrefix . '[' . $idx . ']',
                                                    ])
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Blank-row blueprint. `__ROW__` is substituted with the next
                                         index when bpRepeater.add() clones this template. --}}
                                    <template x-ref="row">
                                        <div data-bp-embed-row
                                             class="relative border border-bp-border-soft/60 rounded-lg p-3 mt-3">
                                            <button type="button"
                                                    @click="$el.closest('[data-bp-embed-row]').remove()"
                                                    class="absolute top-2 right-2 text-bp-muted hover:text-red-400 transition-colors"
                                                    title="Remove">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                            @foreach($embeddedFields as $ef)
                                                @include('bpadmin::components.field-input', [
                                                    'field'      => $ef,
                                                    'value'      => null,
                                                    'errors'     => $errors,
                                                    'definition' => $embeddedDef,
                                                    'namePrefix' => $embeddedPrefix . '[__ROW__]',
                                                ])
                                            @endforeach
                                        </div>
                                    </template>

                                    <button type="button"
                                            data-bp-repeater-add
                                            @click="add()"
                                            class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-bp-primary hover:text-bp-primary/80 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        {{ __('bpadmin::common.buttons.add') }}
                                    </button>
                                </div>
                            @else
                                @php $oldValues = is_array($oldItems) ? $oldItems : []; @endphp
                                @foreach($embeddedFields as $ef)
                                    @include('bpadmin::components.field-input', [
                                        'field'      => $ef,
                                        'value'      => $oldValues[$ef->name()] ?? null,
                                        'errors'     => $errors,
                                        'definition' => $embeddedDef,
                                        'namePrefix' => $embeddedPrefix,
                                    ])
                                @endforeach
                            @endif
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
