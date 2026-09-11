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
                <th class="tw:w-10"></th>
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
                <td class="tw:text-lg tw:p-1 tw:align-middle">
                    {{ $pw->cpwVcID }}
                </td>
                <td>
                    {{ $pw->pw_descr }}
                    <br/><span class="tw:text-xs">{{ $pw->pw_type }} {{ $pw->pw_psntype }}</span>
                </td>
                <td>
                    @if($pw->port)
                        <x-port-link :port="$pw->port" />
                        @if($pw->port->ifOperStatus === \LibreNMS\Enum\IfOperStatus::Up)
                            <i class="fa fa-arrow-up report-up" aria-hidden="true"></i>
                        @elseif($pw->port->ifOperStatus === \LibreNMS\Enum\IfOperStatus::Down)
                            <i class="fa fa-arrow-down report-down" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-question report-warning" aria-hidden="true"></i>
                        @endif
                        <br/><span class="tw:text-xs">{{ $pw->port->ifAlias }}</span>
                        @if($pw->port->ifMtu)
                            <br/><span class="tw:text-xs">MTU {{ $pw->port->ifMtu }}</span>
                        @endif
                        @if($pw->pw_local_mtu != 0)
                            <br/><span class="tw:text-xs">PW MTU {{ $pw->pw_local_mtu }}</span>
                        @endif
                    @endif
                </td>
                <td class="tw:align-middle tw:text-center">
                    <i class="fa fa-arrows-alt tw:text-2xl" aria-hidden="true"></i>
                </td>
                <td class="tw:align-middle">
                    @if($peerPw)
                        @if($peerPw->device)
                            <x-device-link :device="$peerPw->device" />
                        @elseif($pw->peerDevice)
                            <x-device-link :device="$pw->peerDevice" />
                        @endif
                        @if($peerPw->pw_descr)
                            <br/><span class="tw:text-xs">{{ $peerPw->pw_descr }}</span>
                        @endif
                    @elseif($pw->peerDevice)
                        <x-device-link :device="$pw->peerDevice" />
                    @else
                        <span class="tw:italic">{{ __('unresolved remote device') }}</span>
                    @endif
                </td>
                <td>
                    @if($peerPw && $peerPw->port)
                        <x-port-link :port="$peerPw->port" />
                        @if($peerPw->port->ifOperStatus === \LibreNMS\Enum\IfOperStatus::Up)
                            <i class="fa fa-arrow-up report-up" aria-hidden="true"></i>
                        @elseif($peerPw->port->ifOperStatus === \LibreNMS\Enum\IfOperStatus::Down)
                            <i class="fa fa-arrow-down report-down" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-question report-warning" aria-hidden="true"></i>
                        @endif
                        <br/><span class="tw:text-xs">{{ $peerPw->port->ifAlias }}</span>
                        @if($peerPw->port->ifMtu)
                            <br/><span class="tw:text-xs">MTU {{ $peerPw->port->ifMtu }}</span>
                        @endif
                        @if($peerPw->pw_local_mtu != 0)
                            <br/><span class="tw:text-xs">PW MTU {{ $peerPw->pw_local_mtu }}</span>
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
                <td colspan="6" class="text-center tw:p-5">
                    <em>{{ __('No pseudowires found for this device.') }}</em>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</x-device.page>
@endsection
