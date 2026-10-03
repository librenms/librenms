@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('BGP')">
        <x-device.routing-tabs :device="$device" tab="bgp" />

        <x-submenu
            :title="__('Local AS') . ': ' . ($local_as ?? __('N/A')) . ' :: ' . __('BGP')"
            :menu="$bgp_menu"
            :selected="$view"
            :device-id="$device->device_id"
        />

        <x-panel>
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('Local Address') }}</th>
                            <th>{{ __('Peer Address') }}</th>
                            @if($show_vrf)
                                <th>{{ __('VRF') }}</th>
                            @endif
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Family') }}</th>
                            <th>{{ __('Remote AS') }}</th>
                            <th>{{ __('Peer Description') }}</th>
                            <th>{{ __('Admin / State') }}</th>
                            <th>{{ __('Last Error') }}</th>
                            <th>{{ __('Uptime / Updates') }}</th>
                            @if($show_prefixes)
                                <th>{{ __('Prefixes / Limit') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($peers as $peerData)
                        <tr>
                            <td>
                                {{ $peerData['local_addr'] ?: '-' }}
                                @if($peerData['local_port'])
                                    <br>
                                    <x-port-link :port="$peerData['local_port']" />
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('device.routing.bgp', ['device' => $device, 'view' => 'updates']) }}" class="tw:font-bold">
                                    {{ $peerData['identifier_compressed'] }}
                                </a>
                                @if($peerData['linked_port'])
                                    <br>
                                    <x-device-link :device="$peerData['linked_port']->device" tab="routing" section="bgp" />
                                    <x-port-link :port="$peerData['linked_port']" />
                                @endif
                            </td>
                            @if($show_vrf)
                                <td>{{ $peerData['vrf'] ?: '-' }}</td>
                            @endif
                            <td>
                                <span class="{{ $peerData['peer_type_class'] }} tw:font-semibold">{{ $peerData['peer_type'] }}</span>
                            </td>
                            <td class="tw:text-[11px]">
                                {{ $peerData['afi_list'] ?: '-' }}
                            </td>
                            <td>
                                <strong>AS{{ $peerData['remote_as'] }}</strong>
                                @if($peerData['astext'])
                                    <br><small class="text-muted">{{ $peerData['astext'] }}</small>
                                @endif
                            </td>
                            <td>{{ $peerData['descr'] ?: '-' }}</td>
                            <td>
                                <span class="label label-{{ $peerData['admin_color'] }}">{{ $peerData['admin_status'] }}</span>
                                <br>
                                <span class="label label-{{ $peerData['state_color'] }} tw:mt-1 tw:inline-block">{{ $peerData['state'] }}</span>
                            </td>
                            <td class="tw:text-[11px]">
                                {!! nl2br(e($peerData['last_error'])) ?: '-' !!}
                            </td>
                            <td>
                                {{ $peerData['fsm_established_time'] }}
                                <br>
                                <small class="text-muted">
                                    <i class="fa fa-arrow-down text-success" aria-hidden="true"></i> {{ $peerData['in_updates'] }}
                                    <i class="fa fa-arrow-up text-primary" aria-hidden="true"></i> {{ $peerData['out_updates'] }}
                                </small>
                            </td>
                            @if($show_prefixes)
                                <td class="tw:text-[11px] tw:whitespace-nowrap">
                                    @forelse($peerData['prefixes'] as $prefix)
                                        <span class="{{ $prefix['class'] }}">
                                            {{ $prefix['afisafi'] }}: {{ number_format($prefix['accepted']) }}
                                            @if($prefix['limit'])
                                                / {{ number_format($prefix['limit']) }} ({{ $prefix['percent'] }}%)
                                            @endif
                                        </span>
                                        @if(! $loop->last)
                                            <br>
                                        @endif
                                    @empty
                                        -
                                    @endforelse
                                </td>
                            @endif
                        </tr>
                        @if($peerData['show_graph'])
                            <tr>
                                <td colspan="{{ 9 + (int) $show_vrf + (int) $show_prefixes }}" class="tw:bg-[#fdfdfd] dark:tw:bg-dark-gray-300 tw:p-4">
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <x-graph-row columns="4"
                                                :device="$device"
                                                :type="$peerData['graph_type']"
                                                :height="120"
                                                :vars="['id' => $peerData['graph_id']]"
                                            />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ 9 + (int) $show_vrf + (int) $show_prefixes }}" class="tw:text-center tw:p-5">
                                <em>{{ __('No BGP peers found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>
    </x-device.page>
@endsection
