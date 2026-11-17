@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar :name="__('Load Balancer')" :options="$data['options']" :selected="$data['section']" />

    @isset($data['view'])
        @include('device.tabs.loadbalancer.' . $data['view'], $data)
    @endisset

    @foreach($data['graphs'] as $graphType => $graphTitle)
        <x-panel :title="__($graphTitle)">
            <x-graph-row :type="$graphType" :device="$device" :vars="$data['graphVars']" columns="responsive" />
        </x-panel>
    @endforeach
</x-device.page>
@endsection
