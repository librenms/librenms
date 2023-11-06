@extends('layouts.librenmsv1')

@section('title', $title)

@section('content')
    @include($settings_view, $settings)
@endsection
