@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel :title="__('Toner')">
        <x-graph-row :device="$device" :type="'device_toner'" columns="responsive" legend="yes" />
    </x-panel>
</x-device.page>
@endsection
