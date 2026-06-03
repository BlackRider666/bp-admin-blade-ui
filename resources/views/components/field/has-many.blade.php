@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
{{--
    Read-only display for hasMany relations on the edit/create form.
    HasMany relations that need editing are handled via the embedded repeater
    block in create.blade.php / edit.blade.php (isEmbedded check). This view
    handles the non-embedded, display-only case so the form does not 500.
--}}
@include('bpadmin::components.field._relation-list', ['field' => $field, 'value' => $value])
