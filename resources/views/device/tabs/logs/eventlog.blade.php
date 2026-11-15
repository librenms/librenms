@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Eventlog') }}">
        <x-device.log-tabs :device="$device" tab="eventlog" />
        <x-logs.eventlog :device="$device" :device-filter="false" :eventtype="$eventtype" :port="$port" />
    </x-device.page>
@endsection
