@extends('device.submenu')

@section('tabcontent')
    <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th style="width: 150px;">@lang('VLAN Number')</th>
                <th style="width: 250px;">@lang('VLAN Name')</th>
                <th>@lang('Ports')</th>
            </tr>
        </thead>
        <tbody>

        @foreach($data['vlans'] as $vlan_number => $vlans)
            <tr>
                <td>{{ $vlan_number }}</td>
                <td>{{ $vlans->first()->vlan_name }}</td>
                <td>
                @foreach($vlans as $port)
                    @if(!$vars)
@if($port->port)
@portLink($port->port, $port->port->getShortLabel())
@if($port->untagged)
(U)@endif
@if(!$loop->last),
@endif
@endif

                    @else
                    <div style="display: block; padding: 2px; margin: 2px; min-width: 139px; max-width:139px; min-height:85px; max-height:85px; text-align: center; float: left; background-color: {{ \LibreNMS\Config::get('list_colour.odd_alt2') }}">

                        <div style="font-weight: bold;">{{ $port->port->ifDescr }}</div>
                        <a href="{{ route('device', ['device' => $device->device_id, 'tab' => 'port', 'vars' => 'port='.$port->port->port_id]) }}" onmouseover="return overlib('<div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>{{ $device->hostname }}-{{ $port->port->ifDescr }}</div>{{ $port->port->ifAlias }}<img src=\'{{ url('graph.php') }}?type=port_{{ $vars }}&amp;id={{ $port->port->port_id }}&amp;from={{ \LibreNMS\Config::get('time.twoday') }}&amp;to={{ \LibreNMS\Config::get('time.now') }}&amp;width=450&amp;height=150\'>', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);" onmouseout="return nd();">
                                <img src="{{ url('graph.php') }}?type=port_{{ $vars }}&amp;id={{ $port->port->port_id }}&amp;from={{ \LibreNMS\Config::get('time.twoday') }}&amp;to={{ \LibreNMS\Config::get('time.now') }}&amp;width=132&amp;height=40&amp;legend=no">
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
@endsection



