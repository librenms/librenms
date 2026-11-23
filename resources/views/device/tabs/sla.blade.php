@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel :class="$sla->opstatus == 2 ? 'panel-danger' : ''">
        <x-slot:heading>
            <h3 class="panel-title">
                <a href="{{ route('device.slas', ['device' => $device->device_id]) }}">{{ __('SLAs') }}</a> &raquo; {{ $title }}
            </h3>
        </x-slot:heading>
        @foreach($graphs as $type => $graphTitle)
            <x-graph-row :type="$type" :title="__($graphTitle)" :device="$device" :vars="['id' => $sla->sla_id]" columns="responsive" />
        @endforeach
    </x-panel>
</x-device.page>
@endsection
