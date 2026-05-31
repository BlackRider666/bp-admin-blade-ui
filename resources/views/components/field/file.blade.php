@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php $inputName = $name ?? $field->name(); @endphp

<div>
    @if($value)
        <div class="mb-2 flex items-center gap-2 text-sm">
            <svg class="h-4 w-4 text-bp-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
            </svg>
            <a href="{{ \BlackParadise\LaravelAdmin\Support\BPAdminFileUrl::signed('public', $value) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="text-bp-primary hover:text-bp-primary-hover transition-colors underline underline-offset-4 decoration-bp-primary/30 hover:decoration-bp-primary font-mono-bp text-xs truncate">
                {{ basename($value) }}
            </a>
        </div>
    @endif
    <input type="file"
           id="{{ $inputName }}"
           name="{{ $inputName }}"
           class="block w-full text-sm text-bp-gray-600 cursor-pointer rounded-xl border border-bp-border-soft bg-bp-input-bg file:mr-4 file:py-2.5 file:px-4 file:border-0 file:cursor-pointer file:text-xs file:font-semibold file:uppercase file:tracking-wider file:bg-bp-primary/10 file:text-bp-primary hover:file:bg-bp-primary/20 file:transition-colors">
    @if($value)
        <p class="mt-1.5 text-xs text-bp-muted">Upload a new file to replace the current one.</p>
    @endif
</div>
