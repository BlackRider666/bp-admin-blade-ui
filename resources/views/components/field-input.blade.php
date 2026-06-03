@props(['field', 'value' => null, 'errors', 'definition' => null, 'namePrefix' => null])

@php
    $htmlName = $namePrefix
        ? $namePrefix . '[' . $field->name() . ']'
        : $field->name();
    $errorKey = $namePrefix
        ? $namePrefix . '.' . $field->name()
        : $field->name();
@endphp

@if($field->type() === 'hidden')
    @include('bpadmin::components.field.hidden', [
        'field' => $field,
        'value' => old($htmlName, $value),
        'name'  => $htmlName,
    ])
@else
<div class="mb-5">
    <label for="{{ $htmlName }}" class="flex items-center text-[11px] font-semibold uppercase tracking-[0.14em] text-bp-gray-500 mb-2">
        {{ $definition ? bp_field_label($definition, $field) : $field->label() }}
        @if(in_array('required', $field->rules()))
            <span class="text-red-400 ml-1">*</span>
        @endif
    </label>

    @includeFirst([
        'bpadmin::components.field.' . str_replace('_', '-', $field->type()),
        'bpadmin::components.field._unsupported',
    ], [
        'field'    => $field,
        'value'    => old($htmlName, $value),
        'name'     => $htmlName,
        'errorKey' => $errorKey,
    ])

    @error($errorKey)
        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>
@endif
