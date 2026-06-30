@php
    $dt = \BlackParadise\LaravelAdminBladeUI\Support\DateFormatter::parse($value);
@endphp
@if($dt)
    <span class="text-bp-gray-600 text-xs tabular-nums" title="{{ $dt->format('D, d M Y') }}">{{ $dt->format('d.m.Y') }}</span>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
