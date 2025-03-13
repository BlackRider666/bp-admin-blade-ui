@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/js/vendor/bpadmin/main.js'])
@else
    <link rel="stylesheet" crossorigin media="screen, print" href="{{asset('bpadmin/admin.css')}}">
    <link rel="font" media="screen, print" href="{{asset('bpadmin/materialdesignicons-webfont.ttf')}}">
    <link rel="font" media="screen, print" href="{{asset('bpadmin/materialdesignicons-webfont.eot')}}">
    <link rel="font" media="screen, print" href="{{asset('bpadmin/materialdesignicons-webfont.woff')}}">
    <link rel="font" media="screen, print" href="{{asset('bpadmin/materialdesignicons-webfont.woff2')}}">
    <script type="module" crossorigin src="{{asset('bpadmin/admin.js')}}" defer></script>
@endif
