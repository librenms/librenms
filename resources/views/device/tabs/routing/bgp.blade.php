@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('BGP')">
        <x-device.routing-tabs :device="$device" tab="bgp" />

        <x-submenu
            :title="__('Local AS') . ': ' . ($local_as ?? __('N/A')) . ' :: ' . __('BGP')"
            :menu="$bgp_menu"
            :selected="$view"
            :device-id="$device->device_id"
        />

        <x-panel>
            <x-routing.bgp-peer-table :peers="$peers" :graph="$graph" />
        </x-panel>
    </x-device.page>
@endsection
