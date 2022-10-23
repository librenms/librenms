@extends('layouts.librenmsv1')

@section('title', __('service.add'))

@section('content')
    <div class="container">
        <x-panel title="{{ __('service.add') }}">
            @include('service.form')
        </x-panel>
    </div>
@endsection
