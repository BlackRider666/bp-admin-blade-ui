@extends('bpadmin::main')
@section('content')
    @includeWhen(Auth::check(),'bpadmin::_partials.header')
    <v-main>
        @includeWhen(Auth::check(),'bpadmin::_partials.navbar')
        <crud-layout>
            <template v-slot:title>
                @yield('header')
            </template>
            @yield('html')
        </crud-layout>
        @includeWhen(Auth::check(),'bpadmin::_partials.footer')
    </v-main>
@endsection
