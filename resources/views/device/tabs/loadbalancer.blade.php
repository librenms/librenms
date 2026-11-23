@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar :name="__('Load Balancer')" :options="$options" :selected="$section" />

    @isset($page)
        @include('device.tabs.loadbalancer.' . $page)
    @endisset

    @foreach($graphs as $graphType => $graphTitle)
        <x-panel :title="__($graphTitle)">
            <x-graph-row :type="$graphType" :device="$device" :vars="$graphVars" columns="responsive" />
        </x-panel>
    @endforeach
</x-device.page>
@endsection
