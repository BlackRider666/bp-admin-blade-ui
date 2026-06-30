@if($value !== null && $value !== '')
    <a href="tel:{{ $value }}" class="text-blue-600 hover:underline">{{ $value }}</a>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
