@props(['field', 'value' => null, 'name' => null])
@php $inputName = $name ?? $field->name(); @endphp

<input type="hidden"
       id="{{ $inputName }}"
       name="{{ $inputName }}"
       value="{{ $value }}">
