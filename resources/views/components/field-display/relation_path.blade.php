@php
    $resolved = method_exists($record, 'getByPath')
        ? $record->getByPath($field->path())
        : null;
    $displayValue = is_array($resolved) || is_object($resolved)
        ? null  // не знаємо який атрибут — показуємо dash
        : $resolved;
@endphp
@if($displayValue !== null && $displayValue !== '')
    <span class="text-bp-gray-700">{{ \Illuminate\Support\Str::limit((string) $displayValue, 80) }}</span>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
