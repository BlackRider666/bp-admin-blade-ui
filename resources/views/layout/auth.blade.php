@extends('bpadmin::main')
@section('content')
    <v-main>
        <auth-layout>
            <template v-slot:title>
                @yield('header')
            </template>
            @yield('html')
        </auth-layout>
    </v-main>
@endsection
