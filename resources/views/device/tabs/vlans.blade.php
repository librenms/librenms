@extends('device.submenu')

@section('tabcontent')
    <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th style="width: 150px;">{{ __('VLAN Number') }}</th>
                <th style="width: 250px;">{{ __('VLAN Name') }}</th>
                <th>{{ __('Ports') }}</th>
            </tr>
        </thead>
        <tbody>

        @foreach($data['vlans'] as $vlan)
            <tr>
                <td>{{ $vlan->vlan_vlan }}</td>
                <td>{{ $vlan->vlan_name }}</td>
                <td>
                @foreach($vlan->ports as $vlanPort)
                    @if(! $vlanPort->port)
                        @continue;
                    @endif

                    @if(!$vars)
                        <span class="tw:inline-flex">
                            <x-port-link :port="$vlanPort->port">{{ $vlanPort->port->getShortLabel() }}</x-port-link>
                            @if($vlanPort->untagged)<span>&nbsp;(U)</span>@endif
                            @if(!$loop->last)<span>,</span>@endif
                        </span>
                    @else
                        <div style="display: block; padding: 2px; margin: 2px; min-width: 139px; max-width:139px; min-height:85px; max-height:85px; text-align: center; float: left; background-color: {{ \App\Facades\LibrenmsConfig::get('list_colour.odd_alt2') }}">
                            <div style="font-weight: bold;">{{ $vlanPort->port->ifDescr }}</div>
                            <a href="{{ route('device', ['device' => $device->device_id, 'tab' => 'port', 'vars' => 'port='.$vlanPort->port->port_id]) }}" onmouseover="return overlib('<div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>{{ $device->hostname }}-{{ $vlanPort->port->ifDescr }}</div>{{ $vlanPort->port->ifAlias }}<img src=\'{{ url('graph.php') }}?type=port_{{ $vars }}&amp;id={{ $vlanPort->port->port_id }}&amp;from={{ \App\Facades\LibrenmsConfig::get('time.twoday') }}&amp;to={{ \App\Facades\LibrenmsConfig::get('time.now') }}&amp;width=450&amp;height=150\'>', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);" onmouseout="return nd();">
                                <img src="{{ url('graph.php') }}?type=port_{{ $vars }}&amp;id={{ $vlanPort->port->port_id }}&amp;from={{ \App\Facades\LibrenmsConfig::get('time.twoday') }}&amp;to={{ \App\Facades\LibrenmsConfig::get('time.now') }}&amp;width=132&amp;height=40&amp;legend=no">
                            </a>
                            <div style="font-size: 9px;">{{ $vlanPort->port->ifAlias }}</div>
                        </div>
                    @endif
                @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection



