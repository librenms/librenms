@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-qos :qosItems="$device->qos" :deviceId="$device->device_id" :show="$show" />
    </x-device.page>
@endsection
