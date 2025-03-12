<v-form
@foreach ($attributes as $key => $value)
    @if ($key !== 'value' && $key !== 'submit_label')
        {!! $key.'="'.$value.'"' !!}
    @endif
@endforeach
>
{!! csrf_field(); !!}
@foreach ($fields as $field)
    {!! $field->render(); !!}
@endforeach
{!! $submitBtn !!}
</v-form>
