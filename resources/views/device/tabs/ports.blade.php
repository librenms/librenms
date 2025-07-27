@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
    @isset($data['submenu'])
        <x-submenu :title="$title" :menu="$data['submenu']" :device-id="$device_id" :current-tab="$current_tab" :selected="$vars" />
    @endisset

    @includeFirst(['device.tabs.ports.' . $data['tab'], 'device.tabs.ports.detail'])

    </x-device.page>
@endsection
