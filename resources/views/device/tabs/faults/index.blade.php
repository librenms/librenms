@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Faults') }}">
        <x-device.fault-tabs :device="$device" tab="active"/>

        @include('alerts.partials.faults-table', [
            'filterName' => 'device.faults',
            'deviceId' => $device->device_id,
        ])
    </x-device.page>
@endsection
