@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName     = $name ?? $field->name();
    $inputErrorKey = $errorKey ?? $field->name();
    $options       = $field->meta()['options'] ?? [];
@endphp

<select id="{{ $inputName }}"
        name="{{ $inputName }}"
        class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm text-bp-gray-500 focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none
               @error($inputErrorKey) border-red-300 @enderror">
    <option value="">— Select —</option>
    @foreach($options as $optValue => $optLabel)
        <option value="{{ $optValue }}" {{ (string) $value === (string) $optValue ? 'selected' : '' }}>
            {{ $optLabel }}
        </option>
    @endforeach
</select>
