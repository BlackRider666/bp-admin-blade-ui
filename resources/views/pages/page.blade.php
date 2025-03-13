@extends('bpadmin::layout.'.$layout)
@section('title', $title)
@section('header')
    {{ $title }}
    {!! $headers !!}
@endsection
@section('html')
    {!! $html !!}
@endsection
