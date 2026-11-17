@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar :name="__('Graphs')" :options="$groups" :selected="$group" />

    @if($group == 'availability')
        @foreach($availability as $availability)
            <x-panel>
                <x-slot:heading class="tw:flex tw:justify-between">
                    <h3 class="panel-title">{{ \LibreNMS\Util\Time::formatInterval($availability->duration, parts: 1) }}</h3>
                    <span>{{ round($availability->availability_perc, 3) }}%</span>
                </x-slot:heading>
                <x-graph-row type="device_availability" :device="$device" :vars="['duration' => $availability->duration]" columns="responsive" />
            </x-panel>
        @endforeach
    @elseif($group == 'customoid')
        @foreach($customoids as $customoid)
            <x-panel :title="__('Custom OID') . ': ' . $customoid->customoid_descr">
                <x-graph-row :type="'customoid_' . $customoid->customoid_descr" :device="$device" :vars="['unit' => $customoid->customoid_unit ?: 'value']" columns="responsive" />
            </x-panel>
        @endforeach
    @else
        @foreach($graphs as $type => $title)
            <x-panel :title="$title">
                <x-graph-row :type="$type" :device="$device" columns="responsive" />
            </x-panel>
        @endforeach
    @endif
</x-device.page>
@endsection
