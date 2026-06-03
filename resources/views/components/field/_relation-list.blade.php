@php
    /*
     * Shared partial: renders a read-only bullet list of relation items.
     *
     * Expected variables (passed via @include):
     *   $field  — AbstractRelationField instance (must have displayField())
     *   $value  — array|null of relation items (each item is an array or scalar)
     *
     * Translatable display fields (value stored as JSON string or array) are
     * resolved to the current default locale via bp_relation_label().
     */
    $items      = is_array($value) ? $value : [];
    $displayKey = $field->displayField();
    $locale     = app(\BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract::class)->defaultLocale();
@endphp

@if(!empty($items))
    <ul class="space-y-1 text-sm text-bp-gray-700">
        @foreach($items as $item)
            <li class="flex items-center gap-1.5 truncate">
                <span class="h-1 w-1 rounded-full bg-bp-primary/50 flex-shrink-0" aria-hidden="true"></span>
                {{ bp_relation_label($item, $displayKey, $locale) }}
            </li>
        @endforeach
    </ul>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
