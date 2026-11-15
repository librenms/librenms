@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar :name="__('Health')" :options="$data['options']" :selected="$data['metric']" />

    @switch($data['metric'])
        @case('overview')
            @foreach($data['metrics'] as $metric => $title)
                <x-panel>
                    <x-slot:heading><h3 class="panel-title"><a href="{{ $data['options'][$metric]['link'] }}">{{ $title }}</a></h3></x-slot:heading>
                    <x-graph-row :type="'device_' . $metric" :device="$device" columns="responsive" />
                </x-panel>
            @endforeach
            @break
        @case('processor')
        @case('mempool')
        @case('storage')
        @case('diskio')
        @case('qfp')
            @include('device.tabs.health.' . $data['metric'], ['items' => $data['items']])
            @break
        @default
            @include('device.tabs.health.sensors', ['sensors' => $data['items']])
    @endswitch
</x-device.page>
@endsection
