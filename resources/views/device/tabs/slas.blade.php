@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    @isset($data['sla'])
        <x-panel :class="$data['sla']->opstatus == 2 ? 'panel-danger' : ''">
            <x-slot:heading>
                <h3 class="panel-title">
                    <a href="{{ route('device', ['device' => $device->device_id, 'tab' => 'slas']) }}">{{ __('SLAs') }}</a> &raquo; {{ $data['title'] }}
                </h3>
            </x-slot:heading>
            @foreach($data['graphs'] as $type => $title)
                <x-graph-row :type="$type" :title="__($title)" :device="$device" :vars="['id' => $data['sla']->sla_id]" columns="responsive" />
            @endforeach
        </x-panel>
    @else
        <x-panel>
            <x-slot:heading class="tw:flex tw:justify-between tw:flex-wrap">
                <x-option-bar :name="__('SLA')" :options="$data['types']" :selected="$data['type']" border="none" class="tw:inline-block" />
                <x-option-bar :name="__('Status')" :options="$data['statuses']" :selected="$data['status']" border="none" class="tw:inline-block" />
            </x-slot:heading>
        </x-panel>

        @forelse($data['slas'] as $sla)
            <x-panel :class="$sla->opstatus == 2 ? 'panel-danger' : ''">
                <x-slot:heading>
                    <h3 class="panel-title">
                        @if(in_array($sla->rtt_type, $data['detailTypes']))
                            <a href="{{ route('device', ['device' => $device->device_id, 'tab' => 'slas', 'id' => $sla->sla_id]) }}">{{ $data['titles'][$sla->sla_id] }}</a>
                        @else
                            {{ $data['titles'][$sla->sla_id] }}
                        @endif
                    </h3>
                </x-slot:heading>
                <x-graph-row type="device_sla" :device="$device" :vars="['id' => $sla->sla_id]" columns="responsive" />
            </x-panel>
        @empty
            <x-panel><em>{{ __('No SLAs found.') }}</em></x-panel>
        @endforelse
    @endisset
</x-device.page>
@endsection
