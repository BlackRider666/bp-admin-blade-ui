@extends('bpadmin::layout.app')
@section('layout')
    <div class="relative flex min-h-full items-center justify-center py-12 px-4 overflow-hidden">
        <div class="absolute top-4 right-4">
            @include('bpadmin::components.locale-switcher')
        </div>
        <div class="bp-aura"></div>
        <div class="absolute inset-0 bp-grid opacity-60"></div>
        <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-bp-app-bg to-transparent pointer-events-none"></div>

        <div class="w-full max-w-md relative">
            @yield('content')
        </div>
    </div>
@endsection
