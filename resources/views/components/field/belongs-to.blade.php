@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName     = $name ?? $field->name();
    $inputErrorKey = $errorKey ?? $field->name();
    $options       = $field->meta()['options'] ?? [];
    $currentOption = collect($options)->firstWhere('id', $value);
    $currentLabel  = $currentOption['label'] ?? (string) $value;
@endphp

<div x-data="{ search: @js($currentLabel), open: false, selected: @js((string) $value) }" class="relative">
    <input type="hidden" name="{{ $inputName }}" :value="selected">
    <input type="text"
           x-model="search"
           @focus="open = true"
           @click.outside="open = false"
           placeholder="Search..."
           class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none @error($inputErrorKey) border-red-300 @enderror">
    <div x-show="open"
         x-transition
         class="absolute z-10 mt-1 w-full bg-bp-surface rounded-xl shadow-lg border border-bp-border max-h-48 overflow-y-auto">
        @forelse($options as $option)
            <div @click="selected = @js((string) $option['id']); search = @js($option['label']); open = false"
                 class="px-3 py-2 text-sm text-bp-gray-700 hover:bg-bp-primary/5 cursor-pointer">
                {{ $option['label'] }}
            </div>
        @empty
            <div class="px-3 py-2 text-sm text-bp-gray-400">No options available.</div>
        @endforelse
    </div>
</div>
