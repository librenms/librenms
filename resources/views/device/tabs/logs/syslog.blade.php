@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Syslog') }}">
        <x-device.log-tabs :device="$device" tab="syslog" />
        <x-logs.syslog :device="$device" filter-name="device.syslog" :filter="$filter" :filter-fields="$filterFields" :from="$from" :to="$to" />
    </x-device.page>
@endsection
