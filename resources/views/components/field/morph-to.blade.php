@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName   = $name ?? $field->name();
    $fieldMeta   = $field->meta();
    $hasMorphOpt = array_key_exists('morphOptions', $fieldMeta);
    $morphTypes  = $fieldMeta['morphOptions'] ?? [];
    $oldType     = old($inputName . '_type', '');
    $oldId       = old($inputName . '_id', '');
@endphp

@if($hasMorphOpt)
{{-- Form picker: Alpine-driven dual type + id selects (morphOptions key present in meta) --}}
<div
    x-data="{
        types: {{ json_encode($morphTypes) }},
        selectedType: @js((string) $oldType),
        selectedId: @js((string) $oldId),
        get idOptions() {
            const t = this.types.find(x => String(x.value) === String(this.selectedType));
            return t ? t.options : [];
        },
    }"
    class="space-y-2"
>
    <select name="{{ $inputName }}_type"
            x-model="selectedType"
            @change="selectedId = ''"
            class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none @error($inputName . '_type') border-red-300 @enderror">
        <option value="">—</option>
        <template x-for="t in types" :key="t.value">
            <option :value="t.value" x-text="t.label" :selected="String(t.value) === String(selectedType)"></option>
        </template>
    </select>

    <select name="{{ $inputName }}_id"
            x-model="selectedId"
            :disabled="!selectedType"
            class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none disabled:opacity-50 @error($inputName . '_id') border-red-300 @enderror">
        <option value="">—</option>
        <template x-for="o in idOptions" :key="o.id">
            <option :value="o.id" x-text="o.label" :selected="String(o.id) === String(selectedId)"></option>
        </template>
    </select>

    @error($inputName . '_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    @error($inputName . '_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
@else
{{-- Display / read-only fallback: morphOptions absent from meta → display context --}}
@include('bpadmin::components.field._relation-single', ['field' => $field, 'value' => $value])
@endif
