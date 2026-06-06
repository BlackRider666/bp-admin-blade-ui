@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php $inputName = $name ?? $field->name(); @endphp

<div x-data="{ checked: {{ $value ? 'true' : 'false' }} }">
    <input type="hidden" name="{{ $inputName }}" :value="checked ? '1' : '0'">
    <button type="button"
            id="{{ $inputName }}"
            @click="checked = !checked"
            :class="checked ? 'bg-blue-600' : 'bg-bp-gray-200'"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <span :class="checked ? 'translate-x-6' : 'translate-x-1'"
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"></span>
    </button>
    <span class="ml-2 text-sm text-bp-gray-500" x-text="checked ? 'Yes' : 'No'"></span>
</div>
