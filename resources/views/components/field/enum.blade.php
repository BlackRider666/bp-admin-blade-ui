@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName     = $name ?? $field->name();
    $inputErrorKey = $errorKey ?? $field->name();
    $options       = $field->meta()['options'] ?? [];
    $isMultiple    = (bool) ($field->meta()['multiple'] ?? false);
    $selected      = $isMultiple ? array_map(static fn($v) => (string) bp_scalar($v), (array) ($value ?? [])) : null;
@endphp

@if($isMultiple)
    <select id="{{ $inputName }}"
            name="{{ $inputName }}[]"
            multiple
            size="{{ min(max(count($options), 3), 8) }}"
            class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm text-bp-gray-500 focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none
                   @error($inputErrorKey) border-red-300 @enderror">
        @foreach($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ in_array((string) $optValue, $selected, true) ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>
@else
    <select id="{{ $inputName }}"
            name="{{ $inputName }}"
            class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm text-bp-gray-500 focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none
                   @error($inputErrorKey) border-red-300 @enderror">
        <option value="">— Select —</option>
        @foreach($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ (string) bp_scalar($value) === (string) $optValue ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>
@endif
