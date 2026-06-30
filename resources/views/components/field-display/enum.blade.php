@php
    $enumOptions = $field->meta()['options'] ?? [];
    $isMulti     = (bool) ($field->meta()['multiple'] ?? false);
@endphp
@if($isMulti && is_array($value) && count($value))
    <span class="inline-flex flex-wrap gap-1">
        @foreach($value as $item)
            @php $itemKey = bp_scalar($item); @endphp
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-bp-gray-100 text-bp-gray-700">
                {{ $enumOptions[$itemKey] ?? $itemKey }}
            </span>
        @endforeach
    </span>
@elseif(!$isMulti && $value !== null && $value !== '')
    @php $valueKey = bp_scalar($value); @endphp
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-bp-gray-100 text-bp-gray-700">
        {{ $enumOptions[$valueKey] ?? $valueKey }}
    </span>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
