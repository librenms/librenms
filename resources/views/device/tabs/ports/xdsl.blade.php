<x-panel body-class="!tw-p-0">
    <table class="table table-condensed table-striped table-hover tw-mt-1 !tw-mb-0">
        <thead>
        <tr>
            <th>Port</th>
            <th>Traffic</th>
            <th>Sync Speed</th>
            <th>Attainable Speed</th>
            <th>Attenuation</th>
            <th>SNR Margin</th>
            <th>Output Powers</th>
        </tr>
        </thead>
        @foreach($data['adsl'] as $dslPort)
            <tr>
                @include('device.tabs.ports.xdsl_base_columns')
                <td>
                    {{ \LibreNMS\Util\Number::formatSi($dslPort->adslAtucChanCurrTxRate, 2, 3, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($dslPort->adslAturChanCurrTxRate, 2, 3, 'bps') }}
                    <br />
                    <x-graph :port="$dslPort->port" type="port_adsl_speed" width="120" height="40" legend="no"></x-graph>
                </td>
                <td>
                    {{ \LibreNMS\Util\Number::formatSi($dslPort->adslAturCurrAttainableRate, 2, 3, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($dslPort->adslAtucCurrAttainableRate, 2, 3, 'bps') }}
                    <br />
                    <x-graph :port="$dslPort->port" type="port_adsl_attainable" width="120" height="40" legend="no"></x-graph>
                </td>
                <td>
                    {{ $dslPort->adslAturCurrAtn }}dB/{{ $dslPort->adslAtucCurrAtn }}dB
                    <br />
                    <x-graph :port="$dslPort->port" type="port_adsl_attenuation" width="120" height="40" legend="no"></x-graph>
                </td>
                <td>
                    {{ $dslPort->adslAturCurrSnrMgn }}dB/{{ $dslPort->adslAtucCurrSnrMgn }}dB
                    <br />
                    <x-graph :port="$dslPort->port" type="port_adsl_snr" width="120" height="40" legend="no"></x-graph>
                </td>
                <td>
                    {{ $dslPort->adslAturCurrOutputPwr }}dBm/{{ $dslPort->adslAtucCurrOutputPwr }}dBm
                    <br />
                    <x-graph :port="$dslPort->port" type="port_adsl_power" width="120" height="40" legend="no"></x-graph>
                </td>
            </tr>
        @endforeach

        @foreach($data['vdsl'] as $dslPort)
            @include('device.tabs.ports.xdsl_base_columns')
            <td>
                {{ \LibreNMS\Util\Number::formatSi($dslPort->xdsl2ChStatusActDataRateXtur, 2, 3, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($dslPort->xdsl2ChStatusActDataRateXtuc, 2, 3, 'bps') }}
                <br />
                <x-graph :port="$dslPort->port" type="port_vdsl_speed" width="120" height="40" legend="no"></x-graph>
            </td>
            <td>
                {{ \LibreNMS\Util\Number::formatSi($dslPort->xdsl2LineStatusAttainableRateDs, 2, 3, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($dslPort->xdsl2LineStatusAttainableRateUs, 2, 3, 'bps') }}
                <br />
                <x-graph :port="$dslPort->port" type="port_vdsl_attainable" width="120" height="40" legend="no"></x-graph>
            </td>
            <td></td>
            <td></td>
            <td>
                {{ $dslPort->xdsl2LineStatusActAtpDs }}dBm/{{ $dslPort->xdsl2LineStatusActAtpUs }}dBm
                <br />
                <x-graph :port="$dslPort->port" type="port_vdsl_power" width="120" height="40" legend="no"></x-graph>
            </td>
        @endforeach
    </table>
</x-panel>
<?php
return;




$ports = DeviceCache::getPrimary()->ports()->join('ports_vdsl', 'ports.port_id', '=', 'ports_vdsl.port_id')
    ->where('ports.deleted', '0')
    ->orderby('ports.ifIndex', 'ASC')
    ->get();

foreach ($ports as $port) {
    include 'includes/html/print-interface-vdsl.inc.php';
    $i++;
}
echo '</table></div>';
echo "<div style='min-height: 150px;'></div>";
