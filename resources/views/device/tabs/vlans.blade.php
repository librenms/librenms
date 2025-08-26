@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    @isset($data['submenu'])
        <x-submenu :title="$title" :menu="$data['submenu']" :device-id="$device_id" :current-tab="$current_tab" :selected="$vars" />
    @endisset

        <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th style="width: 150px;">{{ __('VLAN Number') }}</th>
                <th style="width: 250px;">{{ __('VLAN Name') }}</th>
                <th>{{ __('Ports') }}</th>
            </tr>
        </thead>
        <tbody>

        @foreach($data['vlans'] as $vlan_number => $vlans)
            <tr>
                <td>{{ $vlan_number }}</td>
                <td>{{ $vlans->first()->vlan_name }}</td>
                <td>
                @foreach($vlans as $port)
                    @if(!$port->port)
                        @continue;
                    @endif

                    @if(!$vars)
                        <span class="tw:inline-flex">
                            <x-port-link :port="$port->port">{{ $port->port->getShortLabel() }}</x-port-link>
                            @if($port->untagged)<span>&nbsp;(U)</span>@endif
                            @if(!$loop->last)<span>,</span>@endif
                        </span>
                    @else
                        <div style="display: block; padding: 2px; margin: 2px; min-width: 139px; max-width:139px; min-height:85px; max-height:85px; text-align: center; float: left; background-color: {{ \App\Facades\LibrenmsConfig::get('list_colour.odd_alt2') }}">
                            <div style="font-weight: bold;">{{ $port->port->ifDescr }}</div>
                            <a href="{{ route('device', ['device' => $device->device_id, 'tab' => 'port', 'vars' => 'port='.$port->port->port_id]) }}" onmouseover="return overlib('<div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>{{ $device->hostname }}-{{ $port->port->ifDescr }}</div>{{ $port->port->ifAlias }}<img src=\'{{ url('graph.php') }}?type=port_{{ $vars }}&amp;id={{ $port->port->port_id }}&amp;from={{ \App\Facades\LibrenmsConfig::get('time.twoday') }}&amp;to={{ \App\Facades\LibrenmsConfig::get('time.now') }}&amp;width=450&amp;height=150\'>', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);" onmouseout="return nd();">
                                <img src="{{ url('graph.php') }}?type=port_{{ $vars }}&amp;id={{ $port->port->port_id }}&amp;from={{ \App\Facades\LibrenmsConfig::get('time.twoday') }}&amp;to={{ \App\Facades\LibrenmsConfig::get('time.now') }}&amp;width=132&amp;height=40&amp;legend=no">
                            </a>
                            <div style="font-size: 9px;">{{ $port->port->ifAlias }}</div>
                        </div>
                    @endif
                @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-device.page>
@endsection



