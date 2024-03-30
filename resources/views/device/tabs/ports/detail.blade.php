<x-panel body-class="!tw-p-0">
    <table id="ports-fdb" class="table table-condensed table-hover table-striped tw-mt-1 !tw-mb-0">
        <thead>
        <tr>
            <th width="350"><A href="sort">Port</a></th>
            <th width="100">Port Groups</th>
            <th width="100"></th>
            <th width="120"><a href="sort">Traffic</a></th>
            <th width="75">Speed</th>
            <th width="100">Media</th>
            <th width="100">Mac Address</th>
            <th width="375"></th>
        </tr>
        </thead>
        @foreach($data['ports'] as $port)
            <tr>
                <td>
                    <x-port-link :port="$port">
                        <span class="tw-text-3xl tw-font-bold"><i class="fa fa-tag" aria-hidden='true'></i> {{ $port->getLabel() }}</span>
                    </x-port-link>
                    <div>
                        @if($port->ifInErrors_delta > 0 || $port->ifOutErrors_delta > 0)
                            <a href="{{ route('device', ['device' => $port->device_id, 'tab' => 'port', 'vars' => 'port=' . $port->port_id]) }}"><i class="fa fa-flag fa-lg tw-text-red-600"></i></a>
                        @endif
                        @if($port->getLabel() !== $port->getDescription())
                            <span class="tw-text-base">{{ $port->getDescription() }}</span>
                        @endif
                    </div>
                    @if($data['tab'] != 'basic')
                        @foreach($port->ipv4 as $ipv4)
                            <div><a class="tw-text-base" href="javascript:popUp('{{ url('ajax/netcmd?cmd=whois&query=' . $ipv4->ipv4_address) }}')">{{ $ipv4->ipv4_address }}/{{ $ipv4->ipv4_prefixlen }}</a></div>
                        @endforeach
                        @foreach($port->ipv6 as $ipv6)
                            <div><a class="tw-text-base" href="javascript:popUp('{{ url('ajax/netcmd?cmd=whois&query=' . $ipv6->ipv6_address) }}')">{{ $ipv4->ipv6_address }}/{{ $ipv4->ipv6_prefixlen }}</a></div>
                        @endforeach
                    @endif
                </td>
                <td>
                    @forelse($port->groups as $group)
                        <div>{{ $group->name }}</div>
                    @empty
                        <div>Default</div>
                    @endforelse
                </td>
                <td>
                    <x-port-link :port="$port" :graphs="$data['graphs']['bits']">
                        <x-graph :port="$port" type="port_bits" width="100" height="20" legend="no"></x-graph>
                    </x-port-link>
                    <x-port-link :port="$port" :graphs="$data['graphs']['upkts']">
                        <x-graph :port="$port" type="port_upkts" width="100" height="20" legend="no"></x-graph>
                    </x-port-link>
                    <x-port-link :port="$port" :graphs="$data['graphs']['errors']">
                        <x-graph :port="$port" type="port_errors" width="100" height="20" legend="no"></x-graph>
                    </x-port-link>
                </td>
                <td>
                    <div>
                        <i class='fa fa-long-arrow-left fa-lg' style='color:green' aria-hidden='true'></i>
                        <span style='color: {{ \LibreNMS\Util\Color::percent($port->in_rate, $port->ifSpeed) }}'>{{ \LibreNMS\Util\Number::formatSi($port->in_rate, 2, 3, 'bps') }}</span>
                    </div>
                    <div>
                        <i class='fa fa-long-arrow-right fa-lg' style='color:blue' aria-hidden='true'></i>
                        <span style='color: {{ \LibreNMS\Util\Color::percent($port->out_rate, $port->ifSpeed) }}'>{{ \LibreNMS\Util\Number::formatSi($port->out_rate, 2, 3, 'bps') }}</span>
                    </div>
                    <div>
                        <i class='fa fa-long-arrow-left fa-lg' style='color:purple' aria-hidden='true'></i>
                        {{ \LibreNMS\Util\Number::formatBi($port->ifInUcastPkts_rate, 2, 3, 'pps') }}
                    </div>
                    <div>
                        <i class='fa fa-long-arrow-right fa-lg' style='color:darkorange' aria-hidden='true'></i>
                        {{ \LibreNMS\Util\Number::formatBi($port->ifOutUcastPkts_rate, 2, 3, 'pps') }}
                    </div>
                </td>
                <td>
                    @if($port->ifSpeed)
                        <div>{{ \LibreNMS\Util\Number::formatSi($port->ifSpeed, 2, 3, 'bps') }}</div>
                    @endif
                    @if($port->ifDuplex != 'unknown')
                        <div>{{ $port->ifDuplex }}</div>
                    @endif
                    @if($port->vlans->isNotEmpty())
                        <div class="tw-text-blue-800">
                            <a href="{{ \LibreNMS\Util\Url::deviceUrl($device->device_id, ['tab' => 'vlans']) }}">
                                @if($port->vlans->count() > 1)
                                    <span title="{{ $port->vlans->pluck('vlan')->implode(',') }}">VLANs: {{ $port->vlans->count() }}</span>
                                @elseif($port->vlans->count() == 1 || $port->ifVlan)
                                    VLAN: {{ $port->vlans->first()->vlan ?: $port->ifVlan }}
                                @elseif($port->ifVrf)
                                    {{ Vrf::where('vrf_id', $port->ifVrf)->value('vrf_name') }}
                                @endif
                            </a>
                        </div>
                    @endif
                </td>
                <td>
                    @if($port->adsl)
                        <div>{{ $port->adsl->adslLineCoding }}/{{ \LibreNMS\Util\Rewrite::dslLineType($port->adsl->adslLineType) }}</div>
                        <div>Sync: {{ \LibreNMS\Util\Number::formatSi($port->adsl->adslAtucChanCurrTxRate, 2, 3, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($port->adsl->adslAturChanCurrTxRate, 2, 3, 'bps') }}</div>
                        <div>Max: {{ \LibreNMS\Util\Number::formatSi($port->adsl->adslAturCurrAttainableRate, 2, 3, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($port->adsl->adslAtucCurrAttainableRate, 2, 3, 'bps') }}</div>
                        <div>Atten: {{ $port->adsl->adslAturCurrAtn }}dB/{{ $port->adsl->adslAtucCurrAtn }}dB</div>
                        <div>SNR: {{ $port->adsl->adslAturCurrSnrMgn }}dB/{{ $port->adsl->adslAtucCurrSnrMgn }}dB</div>
                    @elseif($port->vdsl)
                        <div>Sync: {{ \LibreNMS\Util\Number::formatSi($port->vdsl->xdsl2ChStatusActDataRateXtur, 2, 3, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($port->vdsl->xdsl2ChStatusActDataRateXtuc, 2, 3, 'bps') }}</div>
                        <div>Max: {{ \LibreNMS\Util\Number::formatSi($port->vdsl->xdsl2LineStatusAttainableRateDs, 2, 3, 'bps') }}/{{ \LibreNMS\Util\Number::formatSi($port->vdsl->xdsl2LineStatusAttainableRateUs, 2, 3, 'bps') }}</div>
                    @else
                    <div>{{ \LibreNMS\Util\Rewrite::normalizeIfType($port->ifType) }}</div>
                    @endif

                </td>
                <td>
                    <div class="tw-text-base">{{ $port->ifPhysAddress }}</div>
                    <div class="tw-text-base">MTU {{ $port->ifMtu }}</div>
                </td>
                <td>

                    links, etc
                </td>
            </tr>
        @endforeach
    </table>
</x-panel>
