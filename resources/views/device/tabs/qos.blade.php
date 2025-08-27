@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-qos :qosItems="$device->qos->whereNull('port_id')" :show="$data['show']" />
    </x-device.page>
@endsection
