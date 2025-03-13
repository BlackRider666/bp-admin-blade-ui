<crud-items
        :headers="{{json_encode($headers)}}"
        :items="{{json_encode($items) }}"
        :routes="{{json_encode($routes)}}"
        csrf-token="{{csrf_token()}}"
        searchable="{{$searchable}}"
        :filters="{{json_encode([
        'q' => request()->query('q'),
        'sortBy' => request()->query('sortBy'),
        'sortDesc' => request()->query('sortDesc'),
    ])}}"
></crud-items>
