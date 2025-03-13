<v-btn
@foreach($attributes as $key => $value)
    {!! $key.'="'.$value.'"' !!}
@endforeach
></v-btn>
