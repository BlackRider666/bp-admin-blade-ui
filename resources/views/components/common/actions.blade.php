<action-btns-component
@foreach($attributes as $key => $value)
    {!! $key.'="'.$value.'"' !!}
        @endforeach
></action-btns-component>
