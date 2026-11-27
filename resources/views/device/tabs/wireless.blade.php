@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar :name="__('Wireless')" :options="$options" :selected="$metric?->value ?? 'overview'" />

    @if($metric)
        @foreach($sensors as $sensor)
            <x-panel>
                <x-slot:heading>
                    <h3 class="panel-title">
                        {{ $sensor->sensor_descr }}
                        <span class="pull-right">
                            {{ $sensor->formatValue() }} | {{ $sensor->formatValue('sensor_limit_low') }} &lt;&gt; {{ $sensor->formatValue('sensor_limit') }}
                        </span>
                    </h3>
                </x-slot:heading>
                <x-graph-row :type="$sensor->getGraphType()" :device="$device" :vars="['id' => $sensor->sensor_id]" columns="responsive" />
            </x-panel>
        @endforeach
    @else
        @foreach($classes as $class)
            <x-panel>
                <x-slot:heading>
                    <h3 class="panel-title">
                        <a href="{{ $options[$class->value]['link'] }}">
                            {{ __("wireless.$class->value.long") }}@if(__("wireless.$class->value.unit")) ({{ __("wireless.$class->value.unit") }})@endif
                        </a>
                    </h3>
                </x-slot:heading>
                <x-graph-row :type="'device_wireless_' . $class->value" :device="$device" columns="responsive" />
            </x-panel>
        @endforeach
    @endif
</x-device.page>
@endsection
