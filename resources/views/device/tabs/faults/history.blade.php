@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Alert History') }}">
        <x-device.fault-tabs :device="$device" tab="history"/>

        @include('alerts.partials.history-table', [
            'filterName' => 'device.alert-log',
            'deviceId' => $device->device_id,
        ])
    </x-device.page>
@endsection
