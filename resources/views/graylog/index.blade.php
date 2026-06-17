@extends('layouts.librenmsv1')

@section('title', __('Graylog'))

@section('content')
    <x-panel title="{{ __('Graylog entries') }}">
        @include('graylog._table')
    </x-panel>
@endsection
