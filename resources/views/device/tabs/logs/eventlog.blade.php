@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" subtitle="{{ __('Eventlog') }}">
        <x-device.log-tabs :device="$device" tab="eventlog" />
        <x-logs.eventlog :device="$device" filter-name="device.eventlog" :filter="$filter" :filter-fields="$filterFields" />
    </x-device.page>
@endsection
