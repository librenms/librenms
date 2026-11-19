@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('OTV')">
        <x-device.routing-tabs :device="$device" tab="cisco-otv" />

        <x-panel :title="__('Overlays & Adjacencies')">
            <x-routing.cisco-otv-overlays :components="$components" />
        </x-panel>

        <x-panel id="vlanperoverlay" title="{{ __('AED Enabled VLANs') }}">
            <div class="row">
                <x-graph-row :device="$device" :type="'device_cisco-otv-vlan'" />
            </div>
        </x-panel>

        <x-panel id="macperendpoint" title="{{ __('MAC Addresses') }}">
            <div class="row">
                <x-graph-row :device="$device" :type="'device_cisco-otv-mac'" />
            </div>
        </x-panel>
    </x-device.page>
@endsection
