@props(['field', 'value' => null, 'name' => null, 'errorKey' => null])
{{--
    Read-only display for morphTo relations on the edit/create form.
    Identical rendering logic to has-one — single labelled item.
    Non-embedded case so the form does not 500 on unknown types.
--}}
@include('bpadmin::components.field._relation-single', ['field' => $field, 'value' => $value])
