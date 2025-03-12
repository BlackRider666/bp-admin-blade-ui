<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{config('bpadmin.title')}} | @yield('title')</title>
    <meta name="description" content="Page Title">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
    <meta name="msapplication-tap-highlight" content="no">
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
