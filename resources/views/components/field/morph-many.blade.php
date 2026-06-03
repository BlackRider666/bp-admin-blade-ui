@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
{{--
    Read-only display for morphMany relations on the edit/create form.
    Identical rendering logic to has-many — list of labelled items.
    Non-embedded case so the form does not 500 on unknown types.
--}}
@include('bpadmin::components.field._relation-list', ['field' => $field, 'value' => $value])
