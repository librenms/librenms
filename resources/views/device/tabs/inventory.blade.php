@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    @if($data['type'] === 'entphysical')
        @include('device.tabs.inventory.entphysical')
    @elseif($data['type'] === 'hrdevice')
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{{ __('Host Resources Inventory') }}</h3>
            </div>
            <div class="panel-body">
                @include('device.tabs.inventory.hrdevice')
            </div>
        </div>
    @else
        <div class="alert alert-info">
            {{ __('No inventory data available for this device.') }}
        </div>
    @endif
</x-device.page>
@endsection
