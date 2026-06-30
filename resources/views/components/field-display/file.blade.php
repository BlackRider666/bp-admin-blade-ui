@if($value)
    <a href="{{ \BlackParadise\LaravelAdmin\Support\BPAdminFileUrl::signed('public', $value) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline text-xs">
        {{ basename($value) }}
    </a>
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
