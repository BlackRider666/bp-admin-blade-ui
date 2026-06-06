@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName     = $name ?? $field->name();
    $inputErrorKey = $errorKey ?? $field->name();
    // Deterministic id derived from the input name (no uniqid()), so it survives
    // innerHTML-based repeater cloning and server re-renders without conflict.
    $editorId = 'quill-' . preg_replace('/[^a-z0-9]/i', '-', $inputName);
@endphp

<div x-data="bpQuill(@js($value ?? ''))" x-init="init('{{ $editorId }}')">
    <input type="hidden" id="{{ $editorId }}-input" name="{{ $inputName }}" :value="content">
    <div id="{{ $editorId }}" class="@error($inputErrorKey) ring-1 ring-red-300 @enderror"></div>
</div>
