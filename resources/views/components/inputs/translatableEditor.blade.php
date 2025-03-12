<translatable-editor-input
    @foreach($attributes as $key => $value)
        @if($key !== 'value')
            {!! $key.'="'.$value.'"' !!}
        @else
            @if($value)
                {!! ":".$key."='".json_encode($value)."'" !!}
            @else
                {!! ":".$key."='".json_encode(bpadmin_object_translatable_field(config('bpadmin.languages')))."'" !!}

            @endif
        @endif
    @endforeach
    :languages='{{json_encode(config('bpadmin.languages'))}}'
></translatable-editor-input>
