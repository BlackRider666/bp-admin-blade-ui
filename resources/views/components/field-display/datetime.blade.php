@php
    $dt = \BlackParadise\LaravelAdminBladeUI\Support\DateFormatter::parse($value);
@endphp
@if($dt)
    <span x-data="bpRelativeTime({{ \Illuminate\Support\Js::from($dt->toIso8601String()) }})"
          class="text-bp-gray-600 text-xs tabular-nums inline-flex items-center gap-1.5"
          title="{{ $dt->format('D, d M Y H:i:s') }}">
        <svg class="h-3 w-3 text-bp-muted/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><use href="#bp-i-clock"/></svg>
        <span x-text="label">{{ $dt->format('d.m.Y H:i') }}</span>
    </span>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
