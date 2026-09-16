@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('OTV')">
        <x-device.routing-tabs :device="$device" tab="cisco-otv" />

        <x-panel id="overlays" :title="__('Overlays & Adjacencies')">
            <div class="panel list-group tw:mb-0">
            @forelse($overlays as $overlay)
                <a class="list-group-item {{ $overlay['item_class'] }}" data-toggle="collapse" data-target="#overlay_{{ $overlay['index'] }}" data-parent="#overlays">
                    {{ $overlay['label'] }} - {{ $overlay['transport'] ?? '' }}
                    @if($overlay['is_normal'])
                        <span class="text-success tw:float-right">{{ __('Normal') }}</span>
                    @else
                        <span class="tw:float-right">{{ $overlay['error'] ?? '' }} - <span class="text-danger">{{ __('Alert') }}</span></span>
                    @endif
                </a>
                <div id="overlay_{{ $overlay['index'] }}" class="sublinks collapse">
                @foreach($overlay['adjacencies'] as $adjacency)
                    <a class="list-group-item {{ $adjacency['item_class'] }} small">
                        <i class="fa fa-chevron-right" aria-hidden="true"></i> {{ $adjacency['label'] }} - {{ $adjacency['endpoint'] ?? '' }}
                        @if($adjacency['is_normal'])
                            <span class="text-success tw:float-right">{{ __('Normal') }}</span>
                        @else
                            <span class="tw:float-right">{{ $adjacency['error'] ?? '' }} - <span class="text-danger">{{ __('Alert') }}</span></span>
                        @endif
                    </a>
                @endforeach
                </div>
            @empty
                <div class="list-group-item text-muted">{{ __('No OTV overlays found.') }}</div>
            @endforelse
            </div>
        </x-panel>

        <x-panel id="vlanperoverlay" title="{{ __('AED Enabled VLANs') }}">
            <div class="row">
                <x-graph-row :device="$device" :type="'device_cisco-otv-vlan'" />
            </div>
        </x-panel>

        <x-panel id="macperendpoint" title="{{ __('MAC Addresses') }}">
            <div class="row">
                <x-graph-row :device="$device" :type="'device_cisco-otv-mac'" />
            </div>
        </x-panel>
    </x-device.page>
@endsection
