@if($value)
    <img src="{{ \Illuminate\Support\Facades\Storage::url($value) }}" alt="{{ $definition ? bp_field_label($definition, $field) : $field->label() }}" class="h-10 w-10 rounded object-cover">
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
