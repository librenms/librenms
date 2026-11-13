@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    @if(!empty($data['portui']))
        @include('device.tabs.inventory.portui', ['portui' => $data['portui'], 'device' => $device])
    @endif

    @if($data['type'] === 'entphysical')
        @include('device.tabs.inventory.entphysical')
    @elseif($data['type'] === 'hrdevice')
        <x-panel :title="__('Host Resources Inventory')">
            @include('device.tabs.inventory.hrdevice')
        </x-panel>
    @elseif(empty($data['portui']))
        <div class="alert alert-info">
            {{ __('No inventory data available for this device.') }}
        </div>
    @endif
</x-device.page>
@endsection
