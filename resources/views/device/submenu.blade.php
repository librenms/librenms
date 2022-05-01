@extends('device.index')

@section('tab')
    @isset($data['submenu'])
        <div class="panel panel-default">
            <div class="panel-heading">
                <x-device-submenu :title="$title" :menu="$data['submenu']" :device-id="$device_id" :current-tab="$current_tab" :selected="$vars" />
            </div>
        </div>
    @endisset

    @yield('tabcontent')
@endsection
