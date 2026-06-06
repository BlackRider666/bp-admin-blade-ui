@php
    /*
     * Shared partial: renders a single read-only relation label.
     *
     * Expected variables (passed via @include):
     *   $field  — AbstractRelationField instance (must have displayField())
     *   $value  — array|scalar|null — the relation item
     *
     * Translatable display fields (value stored as JSON string or array) are
     * resolved to the current default locale via bp_relation_label().
     */
    $displayKey = $field->displayField();
    $locale     = app(\BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract::class)->currentLocale();
    $label      = null;

    if (is_array($value) || is_object($value)) {
        $label = bp_relation_label($value, $displayKey, $locale);
    } elseif ($value !== null && $value !== '') {
        $label = (string) $value;
    }
@endphp

@if($label !== null)
    <span class="text-sm text-bp-gray-700">{{ $label }}</span>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
