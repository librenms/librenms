@extends('layouts.librenmsv1')

@section('title', trans('service.add'))

@section('content')
    <div class="container">
        <div class='well well-lg'>
            @include('service.form')
        </div>
    </div>
@endsection
