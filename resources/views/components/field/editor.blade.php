@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName     = $name ?? $field->name();
    $inputErrorKey = $errorKey ?? $field->name();
    // Deterministic id derived from the input name (no uniqid()), so it survives
    // innerHTML-based repeater cloning and server re-renders without conflict.
    $editorId = 'quill-' . preg_replace('/[^a-z0-9]/i', '-', $inputName);
@endphp

@once('bpadmin-quill-assets')
    @push('bp-head')
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"
              integrity="sha384-ecIckRi4QlKYya/FQUbBUjS4qp65jF/J87Guw5uzTbO1C1Jfa/6kYmd6dXUF6D7i" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" defer
                integrity="sha384-utBUCeG4SYaCm4m7GQZYr8Hy8Fpy3V4KGjBZaf4WTKOcwhCYpt/0PfeEe3HNlwx8" crossorigin="anonymous"></script>
    @endpush
@endonce

<div x-data="bpQuill(@js($value ?? ''))" x-init="init('{{ $editorId }}')">
    <input type="hidden" id="{{ $editorId }}-input" name="{{ $inputName }}" :value="content">
    <div id="{{ $editorId }}" class="@error($inputErrorKey) ring-1 ring-red-300 @enderror"></div>
</div>
