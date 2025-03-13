<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{config('bpadmin.title')}} | @yield('title')</title>
    <meta name="description" content="Page Title">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @include('bpadmin::_partials.assets')
</head>
<body>
<div id="app">
    <v-app>
        @yield('content')
    </v-app>
</div>
</body>
</html>
