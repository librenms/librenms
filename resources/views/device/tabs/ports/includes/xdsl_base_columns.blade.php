<td>
    <x-port-link :port="$dslPort->port">{{ $dslPort->port->ifIndex }}. {{ $dslPort->port->getLabel() }}</x-port-link>
    @if($dslPort->port->ifInErrors_delta > 0 || $dslPort->port->ifOutErrors_delta > 0)
        <a href="{{ \LibreNMS\Util\Url::portUrl($dslPort->port, ['graph_type' => 'port_errors']) }}"><i class='fa fa-flag fa-lg tw-text-red-600'></i></a>
    @endif
    @if($dslPort->port->getLabel() !== $dslPort->port->getDescription())
        <br/>{{ $dslPort->port->getDescription() }}
    @endif
</td>
<td>
    {{ \LibreNMS\Util\Number::formatSi($dslPort->port->ifInOctets_rate * 8, 2, 0, 'bps') }}
    <i class='fa fa-arrows-v fa-lg icon-theme' aria-hidden='true'></i>
    {{ \LibreNMS\Util\Number::formatSi($dslPort->port->ifOutOctets_rate * 8, 2, 0, 'bps') }}
    <br />
    <x-graph :port="$dslPort->port" type="port_bits" width="120" height="40" legend="no"></x-graph>
</td>
