@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('BGP')">
        <x-device.routing-tabs :device="$device" tab="bgp" />

        <x-option-bar name="{{ __('BGP') }} ({{ __('Local AS') }}: {{ $local_as ?? __('N/A') }})" :options="$bgp_options" :selected="$view" />

        <x-panel>
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('Peer Address') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Family') }}</th>
                            <th>{{ __('Remote AS') }}</th>
                            <th>{{ __('Peer Description') }}</th>
                            <th>{{ __('Admin / State') }}</th>
                            <th>{{ __('Last Error') }}</th>
                            <th>{{ __('Uptime / Updates') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($peers as $peerData)
                        <tr>
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
                        </tr>
                        @if($peerData['show_graph'])
                            <tr>
                                <td colspan="8" class="tw:bg-[#fdfdfd] dark:tw:bg-dark-gray-300 tw:p-4">
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <x-graph
                                                :device="$device"
                                                :type="$peerData['graph_type']"
                                                :id="$peerData['graph_id']"
                                                :height="120"
                                            />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="tw:text-center tw:p-5">
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
