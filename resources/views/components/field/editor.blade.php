@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
@php
    $inputName     = $name ?? $field->name();
    $inputErrorKey = $errorKey ?? $field->name();
    $editorId      = 'quill-' . $field->name() . '-' . str_replace('.', '-', uniqid('', true));
@endphp

<div>
    <input type="hidden" id="{{ $editorId }}-input" name="{{ $inputName }}" value="{{ $value }}">
    <div id="{{ $editorId }}" class="@error($inputErrorKey) ring-1 ring-red-300 @enderror"></div>
</div>

<script>
(function () {
    var container = document.getElementById('{{ $editorId }}');
    var input     = document.getElementById('{{ $editorId }}-input');
    if (!container || typeof Quill === 'undefined') return;

    var quill = new Quill(container, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['clean'],
            ],
        },
        placeholder: '{{ $field->label() }}...',
    });

    var initial = input.value;
    if (initial) {
        // dangerouslyPasteHTML is Quill's own API for setting initial HTML from
        // a trusted server-side source (stored DB value, not user-supplied input)
        quill.clipboard.dangerouslyPasteHTML(initial);
    }

    quill.on('text-change', function () {
        input.value = quill.getText().trim() ? quill.root.innerHTML : '';
    });
}());
</script>
