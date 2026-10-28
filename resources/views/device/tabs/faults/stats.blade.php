@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Alert Stats') }}">
        <x-device.fault-tabs :device="$device" tab="stats"/>

        @include('alerts.partials.stats-chart')
    </x-device.page>
@endsection
