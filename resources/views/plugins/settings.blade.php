@extends('layouts.librenmsv1')

@section('title', $title)

@section('content')
    @include($content_view, $settings)
@endsection
