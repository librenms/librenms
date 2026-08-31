@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar name="{{ __('Pseudowires') }}" :options="$data['options']" :selected="$data['view']" />

    <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th>{{ __('PW ID') }}</th>
                <th>{{ __('Local PW Name') }}</th>
                <th>{{ __('Local Port') }}</th>
                <th style="width: 40px;"></th>
                <th>{{ __('Remote Device/PW Name') }}</th>
                <th>{{ __('Remote Port') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($data['rows'] as $row)
            @php
                $pw = $row['pw'];
                $peerPw = $row['peerPw'];
            @endphp
            <tr>
                <td style="font-size: 18px; padding: 4px; vertical-align: middle;">
                    {{ $pw->cpwVcID }}
                </td>
                <td>
                    {{ $pw->pw_descr }}
                    <br/><span class="box-desc">{{ $pw->pw_type }} {{ $pw->pw_psntype }}</span>
                </td>
                <td>
                    @if($pw->port)
                        <x-port-link :port="$pw->port" />
                        @if($pw->port->ifOperStatus === 'up')
                            <i class="fa fa-arrow-up report-up" aria-hidden="true"></i>
                        @elseif($pw->port->ifOperStatus === 'down')
                            <i class="fa fa-arrow-down report-down" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-question report-warning" aria-hidden="true"></i>
                        @endif
                        <br/><span class="interface-desc">{{ $pw->port->ifAlias }}</span>
                        @if($pw->port->ifMtu)
                            <br/><span class="box-desc">MTU {{ $pw->port->ifMtu }}</span>
                        @endif
                        @if($pw->pw_local_mtu != 0)
                            <br/><span class="box-desc">PW MTU {{ $pw->pw_local_mtu }}</span>
                        @endif
                    @endif
                </td>
                <td style="vertical-align: middle; text-align: center;">
                    <i class="fa fa-times" aria-hidden="true" style="font-size: 2em;"></i>
                </td>
                <td style="vertical-align: middle;">
                    @if($peerPw)
                        @if($peerPw->device)
                            <x-device-link :device="$peerPw->device" />
                        @elseif($pw->peerDevice)
                            <x-device-link :device="$pw->peerDevice" />
                        @endif
                        @if($peerPw->pw_descr)
                            <br/><span class="box-desc">{{ $peerPw->pw_descr }}</span>
                        @endif
                    @elseif($pw->peerDevice)
                        <x-device-link :device="$pw->peerDevice" />
                    @else
                        <span style="font-style: italic;">{{ __('unresolved remote device') }}</span>
                    @endif
                </td>
                <td>
                    @if($peerPw && $peerPw->port)
                        <x-port-link :port="$peerPw->port" />
                        @if($peerPw->port->ifOperStatus === 'up')
                            <i class="fa fa-arrow-up report-up" aria-hidden="true"></i>
                        @elseif($peerPw->port->ifOperStatus === 'down')
                            <i class="fa fa-arrow-down report-down" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-question report-warning" aria-hidden="true"></i>
                        @endif
                        <br/><span class="interface-desc">{{ $peerPw->port->ifAlias }}</span>
                        @if($peerPw->port->ifMtu)
                            <br/><span class="box-desc">MTU {{ $peerPw->port->ifMtu }}</span>
                        @endif
                        @if($peerPw->pw_local_mtu != 0)
                            <br/><span class="box-desc">PW MTU {{ $peerPw->pw_local_mtu }}</span>
                        @endif
                    @endif
                </td>
            </tr>

            @if($data['view'] === 'minigraphs')
                <tr>
                    <td></td>
                    <td colspan="2">
                        @if($pw->port)
                            @foreach(['port_bits', 'port_upkts', 'port_errors'] as $graph_type)
                                <x-port-link :port="$pw->port">
                                    <x-graph :port="$pw->port" :type="$graph_type" from="-1d" width="150" height="30" legend="no" />
                                </x-port-link>
                            @endforeach
                        @endif
                    </td>
                    <td></td>
                    <td colspan="2">
                        @if($peerPw && $peerPw->port)
                            @foreach(['port_bits', 'port_upkts', 'port_errors'] as $graph_type)
                                <x-port-link :port="$peerPw->port">
                                    <x-graph :port="$peerPw->port" :type="$graph_type" from="-1d" width="150" height="30" legend="no" />
                                </x-port-link>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px;">
                    <em>{{ __('No pseudowires found for this device.') }}</em>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</x-device.page>
@endsection
