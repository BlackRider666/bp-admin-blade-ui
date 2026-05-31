@extends('bpadmin::layout.app')
@section('layout')
    <div class="flex h-full">
        @include('bpadmin::components.sidebar')
        <div class="flex flex-1 flex-col overflow-hidden">
            @include('bpadmin::components.navbar')
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
            @include('bpadmin::components.footer')
        </div>
    </div>
    @include('bpadmin::components.command-palette')
    @include('bpadmin::components.confirm-modal')
    @include('bpadmin::components.toasts')
@endsection
