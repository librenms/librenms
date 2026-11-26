@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <table class="iftable tw:w-full">
        @include('device.tabs.ports.includes.port_row', [
            'collapsing' => false,
            'data' => [
                'neighbors' => $neighbors,
                'neighbor_ports' => null,
                'tab' => 'detail',
                'ports_with_events' => $portsWithEvents,
                'graphs' => [
                    'bits' => [['type' => 'port_bits', 'title' => __('Traffic'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                    'upkts' => [['type' => 'port_upkts', 'title' => __('Packets (Unicast)'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                    'errors' => [['type' => 'port_errors', 'title' => __('Errors'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                ],
            ],
        ])
    </table>

    <x-panel>
        <x-slot:heading class="tw:flex tw:flex-wrap tw:justify-between tw:gap-2">
            <div class="tw:flex tw:flex-wrap tw:gap-x-4">
                @foreach($menus as $menu)
                    <x-option-bar :name="$menu['name']" :options="$menu['options']" :selected="$menu['selected']" border="none" />
                @endforeach
            </div>
            @if($bill)
                <a href="{{ $bill['link'] }}"><i class="fa fa-money fa-lg icon-theme" aria-hidden="true"></i> {{ $bill['text'] }}</a>
            @endif
        </x-slot:heading>
    </x-panel>

    @include('device.tabs.port.' . $view, $viewData)
</x-device.page>
@endsection
