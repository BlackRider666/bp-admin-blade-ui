<v-btn
@foreach($attributes as $key => $value)
    {!! $key.'="'.$value.'"' !!}
@endforeach
>
@if($this->icon)
    <v-icon>{{$icon}}</v-icon>
@endif
{{$label}}
</v-btn>
