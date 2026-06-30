@if($value !== null && $value !== '')
    <span class="tabular-nums text-bp-gray-700">{{ $value }}</span>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
