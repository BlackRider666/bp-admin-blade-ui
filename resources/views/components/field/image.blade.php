@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php $inputName = $name ?? $field->name(); @endphp

<div x-data="{ preview: @js($value ? \Illuminate\Support\Facades\Storage::url($value) : '') }">
    @if($value)
        <div class="mb-3">
            <img :src="preview" src="{{ \Illuminate\Support\Facades\Storage::url($value) }}" alt="current"
                 class="h-24 w-24 rounded-xl object-cover border border-bp-border-soft shadow-card">
        </div>
    @else
        <div class="mb-3 h-24 w-24 rounded-xl border border-dashed border-bp-border-soft bg-bp-surface-2/60 flex items-center justify-center text-bp-muted"
             x-show="!preview" x-cloak>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <template x-if="preview">
            <div class="mb-3">
                <img :src="preview" alt="preview" class="h-24 w-24 rounded-xl object-cover border border-bp-border-soft shadow-card">
            </div>
        </template>
    @endif
    <input type="file"
           id="{{ $inputName }}"
           name="{{ $inputName }}"
           accept="image/*"
           @change="preview = URL.createObjectURL($event.target.files[0])"
           class="block w-full text-sm text-bp-gray-600 cursor-pointer rounded-xl border border-bp-border-soft bg-bp-input-bg file:mr-4 file:py-2.5 file:px-4 file:border-0 file:cursor-pointer file:text-xs file:font-semibold file:uppercase file:tracking-wider file:bg-bp-primary/10 file:text-bp-primary hover:file:bg-bp-primary/20 file:transition-colors">
    @if($value)
        <p class="mt-1.5 text-xs text-bp-muted">Upload a new image to replace the current one.</p>
    @endif
</div>
