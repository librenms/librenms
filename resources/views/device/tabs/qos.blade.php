@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-qos :qosItems="$device->qos" :show="$show" />
    </x-device.page>
@endsection
