@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel>
        <x-slot:heading class="tw:flex tw:justify-between tw:flex-wrap">
            <x-option-bar :name="__('SLA')" :options="$types" :selected="$type" border="none" class="tw:inline-block" />
            <x-option-bar :name="__('Status')" :options="$statuses" :selected="$status" border="none" class="tw:inline-block" />
        </x-slot:heading>
    </x-panel>

    @forelse($slas as $sla)
        <x-panel :class="$sla->opstatus == 2 ? 'panel-danger' : ''">
            <x-slot:heading>
                <h3 class="panel-title">
                    @if(in_array($sla->rtt_type, $detailTypes))
                        <a href="{{ route('device.slas.show', ['device' => $device->device_id, 'sla' => $sla->sla_id]) }}">{{ $titles[$sla->sla_id] }}</a>
                    @else
                        {{ $titles[$sla->sla_id] }}
                    @endif
                </h3>
            </x-slot:heading>
            <x-graph-row type="device_sla" :device="$device" :vars="['id' => $sla->sla_id]" columns="responsive" />
        </x-panel>
    @empty
        <x-panel><em>{{ __('No SLAs found.') }}</em></x-panel>
    @endforelse
</x-device.page>
@endsection
