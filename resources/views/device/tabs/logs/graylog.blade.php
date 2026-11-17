@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Graylog') }}">
        <x-device.log-tabs :device="$device" tab="graylog" />
        <x-logs.graylog :device="$device" :device-filter="false" :stream="$stream" :range="$range" :loglevel="$loglevel" />
    </x-device.page>
@endsection
