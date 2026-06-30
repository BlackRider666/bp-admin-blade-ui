@php
    $relationName = $field->relationName();
    $displayField = $field->displayField();
    $relation = $record->relation($relationName);
    if (
        $field instanceof \BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractRelationField
        && $field->hasDisplayCallback()
        && is_array($relation)
    ) {
        // Обчислювана мітка (closure над deep-серіалізованим рядком relation).
        $displayValue = $field->resolveDisplayLabel($relation, $displayField);
    } else {
        // bp_relation_label() handles translatable display fields (array/JSON)
        // so they render as a locale string rather than the raw array.
        $displayValue = $relation !== null
            ? bp_relation_label($relation, $displayField, $bpLocale)
            : (string) ($value ?? '');
    }
@endphp
{{ $displayValue }}
