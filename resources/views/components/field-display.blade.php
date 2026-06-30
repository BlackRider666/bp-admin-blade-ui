@props(['field', 'record', 'definition' => null])
@php
    $value = $record->get($field->name());
    // Locale resolved once per render; injected into every type partial via @includeFirst below.
    $bpLocale = bp_locale();
@endphp

@includeFirst(
    ['bpadmin::components.field-display.' . $field->type(), 'bpadmin::components.field-display._default'],
    ['field' => $field, 'record' => $record, 'value' => $value, 'definition' => $definition ?? null, 'bpLocale' => $bpLocale]
)
