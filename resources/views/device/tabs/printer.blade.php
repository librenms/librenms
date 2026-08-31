@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ __('Toner') }}</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <x-graph-row :device="$device" :type="'device_toner'" />
            </div>
        </div>
    </div>
</x-device.page>
@endsection
