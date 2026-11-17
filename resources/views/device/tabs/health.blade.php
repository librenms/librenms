@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar :name="__('Health')" :options="$options" :selected="$metric" />

    @switch($metric)
        @case('overview')
            @foreach($metrics as $name => $title)
                <x-panel>
                    <x-slot:heading><h3 class="panel-title"><a href="{{ $options[$name]['link'] }}">{{ $title }}</a></h3></x-slot:heading>
                    <x-graph-row :type="'device_' . $name" :device="$device" columns="responsive" />
                </x-panel>
            @endforeach
            @break
        @case('processor')
        @case('mempool')
        @case('storage')
        @case('diskio')
        @case('qfp')
            @include('device.tabs.health.' . $metric)
            @break
        @default
            @include('device.tabs.health.sensors', ['sensors' => $items])
    @endswitch
</x-device.page>
@endsection
