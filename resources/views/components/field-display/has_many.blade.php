@php
    $relItems = $record->relation($field->relationName()) ?? [];
    if (!is_array($relItems)) { $relItems = []; }
    $displayKey = $field->displayField();
@endphp
@if(!empty($relItems))
    <ul class="flex flex-col gap-0.5 text-sm text-bp-gray-700">
        @foreach($relItems as $item)
            <li class="truncate">{{ bp_relation_label($item, $displayKey, $bpLocale) }}</li>
        @endforeach
    </ul>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
