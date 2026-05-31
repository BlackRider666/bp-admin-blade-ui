@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName     = $name ?? $field->name();
    $inputErrorKey = $errorKey ?? $field->name();
    $options       = $field->meta()['options'] ?? [];
    if (is_array($value)) {
        $selected = array_map(
            fn ($v) => is_array($v) ? (string) ($v['id'] ?? '') : (string) $v,
            $value,
        );
    } elseif ($value !== null && $value !== '') {
        $selected = array_map('strval', explode(',', (string) $value));
    } else {
        $selected = [];
    }
    $selected = array_values(array_filter($selected, fn ($s) => $s !== ''));
@endphp

<div
    x-data="{
        options:  {{ json_encode($options) }},
        selected: {{ json_encode($selected) }},
        search:   '',
        open:     false,
        toggle(id) {
            id = String(id);
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(s => s !== id);
            } else {
                this.selected.push(id);
            }
        },
        labelFor(id) {
            const opt = this.options.find(o => String(o.id) === String(id));
            return opt ? opt.label : id;
        },
        get filteredOptions() {
            if (!this.search) return this.options;
            return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase()));
        },
    }"
    class="relative"
>
    {{-- Hidden inputs for form submission (array) --}}
    <template x-for="id in selected" :key="id">
        <input type="hidden" name="{{ $inputName }}[]" :value="id">
    </template>
    {{-- Ensure an empty array is submitted when nothing selected --}}
    <template x-if="selected.length === 0">
        <input type="hidden" name="{{ $inputName }}[]" value="">
    </template>

    {{-- Trigger area --}}
    <div @click="open = !open"
         class="min-h-[40px] w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 cursor-pointer flex flex-wrap gap-1.5 items-center
                @error($inputErrorKey) border-red-300 @enderror">
        <template x-for="id in selected" :key="id">
            <span class="inline-flex items-center gap-1 bg-bp-primary/10 text-bp-primary text-xs font-medium px-2 py-0.5 rounded-full">
                <span x-text="labelFor(id)"></span>
                <button type="button" @click.stop="toggle(id)"
                        class="hover:text-red-500 leading-none font-bold">&times;</button>
            </span>
        </template>
        <span x-show="selected.length === 0" class="text-bp-gray-400 text-sm select-none">Select...</span>
    </div>

    {{-- Dropdown --}}
    <div x-show="open"
         x-cloak
         @click.outside="open = false"
         x-transition
         class="absolute z-20 mt-1 w-full bg-bp-surface rounded-xl shadow-lg border border-bp-border">
        <div class="p-2">
            <input type="text"
                   x-model="search"
                   placeholder="Search..."
                   class="w-full rounded-lg border border-bp-border px-3 py-1.5 text-sm focus:border-bp-primary focus:outline-none">
        </div>
        <div class="max-h-48 overflow-y-auto divide-y divide-bp-gray-50">
            <template x-for="opt in filteredOptions" :key="opt.id">
                <div @click="toggle(opt.id)"
                     :class="selected.includes(String(opt.id)) ? 'bg-bp-primary/5 text-bp-primary' : 'text-bp-gray-700 hover:bg-bp-surface'"
                     class="flex items-center justify-between px-3 py-2 text-sm cursor-pointer transition-colors">
                    <span x-text="opt.label"></span>
                    <svg x-show="selected.includes(String(opt.id))"
                         class="h-4 w-4 text-bp-primary flex-shrink-0"
                         fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                    </svg>
                </div>
            </template>
            <div x-show="filteredOptions.length === 0"
                 class="px-3 py-3 text-sm text-bp-gray-400 text-center">
                No results.
            </div>
        </div>
    </div>
</div>
