@php
    $relItems = $record->relation($field->relationName()) ?? [];
    if (!is_array($relItems)) { $relItems = []; }
    $displayKey = $field->displayField();
@endphp
@if(!empty($relItems))
    <div class="flex flex-wrap gap-1">
        @foreach($relItems as $item)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-bp-primary/10 text-bp-primary">
                {{ bp_relation_label($item, $displayKey, $bpLocale) }}
            </span>
        @endforeach
    </div>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
