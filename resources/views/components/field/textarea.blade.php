@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php $inputName = $name ?? $field->name(); $inputErrorKey = $errorKey ?? $field->name(); @endphp

<textarea id="{{ $inputName }}"
          name="{{ $inputName }}"
          rows="{{ $field->meta()['rows'] ?? 4 }}"
          placeholder="{{ $field->label() }}…"
          class="block w-full rounded-xl border border-bp-border bg-bp-surface px-3 py-2 text-sm text-bp-gray-500 resize-y focus:border-bp-primary focus:ring-1 focus:ring-bp-primary focus:outline-none @error($inputErrorKey) border-red-300 @enderror">{{ $value }}</textarea>
