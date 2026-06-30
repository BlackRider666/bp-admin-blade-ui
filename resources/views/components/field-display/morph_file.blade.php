@php
    // Normalisation shared with morph-file.blade.php via bp_morph_file_meta().
    ['path' => $mfPath, 'name' => $mfName, 'isImage' => $mfIsImage]
        = bp_morph_file_meta($value);
@endphp
@if($mfPath)
    @if($mfIsImage)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($mfPath) }}"
             alt="{{ $mfName ?? basename($mfPath) }}"
             class="h-10 w-10 rounded object-cover">
    @else
        <a href="{{ \BlackParadise\LaravelAdmin\Support\BPAdminFileUrl::signed('public', $mfPath) }}"
           target="_blank"
           rel="noopener noreferrer"
           class="text-bp-primary hover:text-bp-primary-hover underline underline-offset-4 text-xs font-mono-bp">
            {{ $mfName ?? basename($mfPath) }}
        </a>
    @endif
@else
    <span class="text-bp-gray-400 text-xs">—</span>
@endif
