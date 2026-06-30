@php
    $selectOptions = $field->meta()['options'] ?? [];
    // Guard the options lookup: only a scalar value is a valid array offset.
    // A non-scalar value (e.g. an array) would raise a TypeError → 500, so it
    // falls back to the placeholder instead.
    $selectLabel   = is_scalar($value) ? ($selectOptions[$value] ?? $value) : null;
@endphp
@if($selectLabel !== null && $selectLabel !== '')
    <span class="text-bp-gray-700">{{ $selectLabel }}</span>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
