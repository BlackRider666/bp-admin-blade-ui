@php
    $relItem = $record->relation($field->relationName());
    $displayKey = $field->displayField();
@endphp
@if(!empty($relItem))
    <span class="text-bp-gray-700">{{ bp_relation_label($relItem, $displayKey, $bpLocale) }}</span>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
