@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    @if($data['type'] === 'entphysical')
        @include('device.tabs.inventory.entphysical')
    @elseif($data['type'] === 'hrdevice')
        <x-panel :title="__('Host Resources Inventory')">
            @include('device.tabs.inventory.hrdevice')
        </x-panel>
    @else
        <div class="alert alert-info">
            {{ __('No inventory data available for this device.') }}
        </div>
    @endif
</x-device.page>
@endsection
