@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
{{--
    Read-only display for hasOne relations on the edit/create form.
    HasOne relations that need editing are handled via the embedded single-block
    in create.blade.php / edit.blade.php (isEmbedded check). This view
    handles the non-embedded, display-only case so the form does not 500.
--}}
@include('bpadmin::components.field._relation-single', ['field' => $field, 'value' => $value])
