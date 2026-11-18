@extends('layouts.librenmsv1')

@section('title', __('Alert Rules'))

@section('content')
    @if($device)
        <x-device.page :device="$device">
            <x-device.edit-tabs :device="$device" tab="alert-rules" />
            @include('alert-rules.list')
        </x-device.page>
    @else
        <div class="container-fluid">
            @include('alert-rules.list')
        </div>
    @endif
@endsection
