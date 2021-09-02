@extends('device.index')

@section('tab')
    @isset($data['submenu'])
        <x-submenu :title="$title" :menu="$data['submenu']" :device-id="$device_id" :current-tab="$current_tab" :selected="$vars" />
    @endisset

    @yield('tabcontent')
@endsection
