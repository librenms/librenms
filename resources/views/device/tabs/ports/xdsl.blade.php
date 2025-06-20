<x-panel body-class="tw:p-0!">
    <table class="table table-condensed table-striped table-hover tw:mt-1 tw:mb-0!">
        <thead>
        <tr>
            <th>{{ __('Port') }}</th>
            <th>{{ __('Traffic') }}</th>
            <th>{{ __('port.xdsl.sync') }}</th>
            <th>{{ __('port.xdsl.attainable') }}</th>
            <th>{{ __('port.xdsl.attenuation') }}</th>
            <th>{{ __('port.xdsl.snr') }}</th>
            <th>{{ __('port.xdsl.power') }}</th>
        </tr>
        </thead>
        @foreach($data['adsl'] as $dslPort)
            <tr>
                @include('device.tabs.ports.includes.xdsl_base_columns')
                <td>
                    {{ \LibreNMS\Util\Number::formatSi($dslPort->adslAtucChanCurrTxRate, 2, 0, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($dslPort->adslAturChanCurrTxRate, 2, 0, 'bps') }}
                    <br />
                    <x-graph :port="$dslPort->port" type="port_adsl_speed" width="120" height="40" legend="no"></x-graph>
                </td>
                <td>
                    {{ \LibreNMS\Util\Number::formatSi($dslPort->adslAtucCurrAttainableRate, 2, 0, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($dslPort->adslAturCurrAttainableRate, 2, 0, 'bps') }}
                    <br />
                    <x-graph :port="$dslPort->port" type="port_adsl_attainable" width="120" height="40" legend="no"></x-graph>
                </td>
                <td>
                    {{ $dslPort->adslAtucCurrAtn }}dB/{{ $dslPort->adslAturCurrAtn }}dB
                    <br />
                    <x-graph :port="$dslPort->port" type="port_adsl_attenuation" width="120" height="40" legend="no"></x-graph>
                </td>
                <td>
                    {{ $dslPort->adslAtucCurrSnrMgn }}dB/{{ $dslPort->adslAturCurrSnrMgn }}dB
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
            @include('device.tabs.ports.includes.xdsl_base_columns')
            <td>
                {{ \LibreNMS\Util\Number::formatSi($dslPort->xdsl2ChStatusActDataRateXtuc, 2, 0, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($dslPort->xdsl2ChStatusActDataRateXtur, 2, 0, 'bps') }}
                <br />
                <x-graph :port="$dslPort->port" type="port_vdsl_speed" width="120" height="40" legend="no"></x-graph>
            </td>
            <td>
                {{ \LibreNMS\Util\Number::formatSi($dslPort->xdsl2LineStatusAttainableRateDs, 2, 0, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($dslPort->xdsl2LineStatusAttainableRateUs, 2, 0, 'bps') }}
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
