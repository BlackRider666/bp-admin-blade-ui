<submit-btn-component
    @foreach($attributes as $key => $value)
        {!! $key.'="'.$value.'"' !!}
    @endforeach
></submit-btn-component>
