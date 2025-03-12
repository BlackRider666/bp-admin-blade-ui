@extends('bpadmin::layout.'.$layout)
@section('title', $page['title'])
@section('header')
    {{$page['title']}}
    {!! $page['headers'] !!}
@endsection
@section('html')
    {!! $page['html'] !!}
@endsection
