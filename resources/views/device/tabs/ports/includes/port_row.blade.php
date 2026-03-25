<tr>
    <td>{{ $port->ifIndex }}</td>
    <td>
        <div>
            <x-port-link :port="$port" class="tw:inline">
                <span class="tw:text-3xl tw:font-bold"><i class="fa fa-tag" aria-hidden='true'></i> {{ $port->getLabel() }}</span>
            </x-port-link>
            @if($data['tab'] != 'basic')
            @foreach($port->transceivers as $transceiver)
                @php
                    $transceiver->setRelation('port', $port); // save a query
                @endphp
                <x-popup>
                    <a href="{{ \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $port->device_id, 'tab' => 'port','port' => $port->port_id], ['view' => 'transceiver']) }}" class="tw:text-current">
                        <span class="tw:ml-3 tw:text-3xl"><x-icons.transceiver/></span>
                    </a>
                    <x-slot name="body" class="tw:p-0">
                        @if(array_filter($transceiver->only(['type', 'vendor', 'model', 'revision', 'serial', 'data', 'ddm', 'encoding', 'cable', 'distance', 'wavelength', 'connector'])))
                            <div class="tw:opacity-90 tw:p-4 tw:border-b-2 tw:border-solid tw:border-gray-200 tw:dark:border-dark-gray-200 tw:rounded-t-lg">
                                <x-transceiver :transceiver="$transceiver" :portlink="false"></x-transceiver>
                            </div>
                        @endif
                        <x-transceiver-sensors :transceiver="$transceiver" class="tw:p-3"></x-transceiver-sensors>
                    </x-slot>
                </x-popup>
            @endforeach
            @endif
        </div>
        <div>
            @if($port->ifInErrors_delta > 0 || $port->ifOutErrors_delta > 0)
                <a href="{{ route('device', ['device' => $port->device_id, 'tab' => 'port', 'vars' => 'port=' . $port->port_id]) }}"><i class="fa fa-flag fa-lg tw:text-red-600"></i></a>
            @endif
            @if($port->getLabel() !== $port->getDescription())
                <span class="tw:text-base">{{ $port->getDescription() }}</span>
            @endif
        </div>
        @if($data['tab'] != 'basic')
            @foreach($port->ipv4 as $ipv4)
                <div class="tw:text-base">{{ $ipv4->ipv4_address }}/{{ $ipv4->ipv4_prefixlen }}</div>
            @endforeach
            @foreach($port->ipv6 as $ipv6)
                <div class="tw:text-base">{{ $ipv6->ipv6_compressed }}/{{ $ipv6->ipv6_prefixlen }}</div>
            @endforeach
        @endif
        @if($port->portSecurity)
            <span class="tw:text-sm tw:text-gray-500">
                <i class="fa fa-lg {{ \LibreNMS\Enum\PortSecurityStatus::getIconClass($port->portSecurity->status) }}" aria-hidden='true' title="Port Security Status: {{ $port->portSecurity->status }}"></i>
            </span>
        @endif
    </td>
    <td @if($collapsing)class="tw:hidden tw:md:table-cell"@endif>
        @forelse($port->groups as $group)
            <div>{{ $group->name }}</div>
        @empty
            <div>{{ __('Default') }}</div>
        @endforelse
    </td>
    <td>
        <div class="tw:flex tw:flex-col">
        <x-port-link :port="$port" :graphs="$data['graphs']['bits']">
            <x-graph :port="$port" type="port_bits" width="100" height="20" legend="no"></x-graph>
        </x-port-link>
        <x-port-link :port="$port" :graphs="$data['graphs']['upkts']">
            <x-graph :port="$port" type="port_upkts" width="100" height="20" legend="no"></x-graph>
        </x-port-link>
        <x-port-link :port="$port" :graphs="$data['graphs']['errors']">
            <x-graph :port="$port" type="port_errors" width="100" height="20" legend="no"></x-graph>
        </x-port-link>
        </div>
    </td>
    <td class="tw:whitespace-nowrap">
        <div>
            <i class="fa fa-long-arrow-left fa-lg tw:text-green-600" aria-hidden="true"></i>
            <span style="color: {{ \LibreNMS\Util\Color::percent($port->in_rate, $port->ifSpeed) }}">{{ \LibreNMS\Util\Number::formatSi($port->ifInOctets_rate * 8, 2, 0, 'bps') }}</span>
        </div>
        <div>
            <i class="fa fa-long-arrow-right fa-lg" style="color:blue" aria-hidden="true"></i>
            <span style="color: {{ \LibreNMS\Util\Color::percent($port->out_rate, $port->ifSpeed) }}">{{ \LibreNMS\Util\Number::formatSi($port->ifOutOctets_rate * 8, 2, 0, 'bps') }}</span>
        </div>
        <div>
            <i class="fa fa-long-arrow-left fa-lg" style="color:purple" aria-hidden="true"></i>
            {{ \LibreNMS\Util\Number::formatBi($port->ifInUcastPkts_rate, 2, 0, 'pps') }}
        </div>
        <div>
            <i class="fa fa-long-arrow-right fa-lg" style="color:darkorange" aria-hidden="true"></i>
            {{ \LibreNMS\Util\Number::formatBi($port->ifOutUcastPkts_rate, 2, 0, 'pps') }}
        </div>
    </td>
    <td class="tw:whitespace-nowrap">
        @if($port->ifSpeed)
            <div>{{ \LibreNMS\Util\Number::formatSi($port->ifSpeed, 2, 0, 'bps') }}</div>
        @endif
        @if($port->ifDuplex != 'unknown')
            <div>{{ $port->ifDuplex }}</div>
        @endif
        @if($port->vlans->isNotEmpty())
            <div class="tw:text-blue-800">
                <a href="{{ \LibreNMS\Util\Url::deviceUrl($port->device_id, ['tab' => 'vlans']) }}">
                    @if($port->vlans->count() > 1)
                        <span title="{{ $port->vlans->sortby('vlan')->pluck('vlan')->implode(',') }}">{{ __('port.vlan_count', ['count' => $port->vlans->count()]) }}</span>
                    @elseif($port->vlans->count() == 1 || $port->ifVlan)
                        {{ __('port.vlan_label', ['label' => $port->vlans->first()->vlan ?: $port->ifVlan]) }}
                    @endif
                </a>
            </div>
        @endif
        @if($port->ifVrf)
            <div>
                {{ __('port.vrf_label', ['name' => $port->vrf?->vrf_name]) }}
            </div>
        @endif
    </td>
    <td @if($collapsing)class="tw:hidden tw:sm:table-cell"@endif>
        @if($port->adsl)
            <div>{{ $port->adsl->adslLineCoding }}/{{ \LibreNMS\Util\Rewrite::dslLineType($port->adsl->adslLineType) }}</div>
            <div>{{ __('port.xdsl.sync_stat', ['down' => \LibreNMS\Util\Number::formatSi($port->adsl->adslAtucChanCurrTxRate, 2, 0, 'bps'), 'up' => \LibreNMS\Util\Number::formatSi($port->adsl->adslAturChanCurrTxRate, 2, 0, 'bps')]) }}</div>
            <div>{{ __('port.xdsl.attainable_stat', ['down' => \LibreNMS\Util\Number::formatSi($port->adsl->adslAtucCurrAttainableRate, 2, 0, 'bps'), 'up' => \LibreNMS\Util\Number::formatSi($port->adsl->adslAturCurrAttainableRate, 2, 0, 'bps')]) }}</div>
            <div>{{ __('port.xdsl.attenuation_stat', ['down' => $port->adsl->adslAtucCurrAtn . 'dB', 'up' => $port->adsl->adslAturCurrAtn . 'dB']) }}</div>
            <div>{{ __('port.xdsl.snr_stat', ['down' => $port->adsl->adslAtucCurrSnrMgn . 'dB','up' => $port->adsl->adslAturCurrSnrMgn . 'dB']) }}</div>
        @elseif($port->vdsl)
            <div>{{ __('port.xdsl.sync_stat', ['down' => \LibreNMS\Util\Number::formatSi($port->vdsl->xdsl2ChStatusActDataRateXtuc, 2, 0, 'bps'), 'up' => \LibreNMS\Util\Number::formatSi($port->vdsl->xdsl2ChStatusActDataRateXtur, 2, 0, 'bps')]) }}</div>
            <div>{{ __('port.xdsl.attainable_stat', ['down' => \LibreNMS\Util\Number::formatSi($port->vdsl->xdsl2LineStatusAttainableRateDs, 2, 0, 'bps'), 'up' => \LibreNMS\Util\Number::formatSi($port->vdsl->xdsl2LineStatusAttainableRateUs, 2, 0, 'bps')]) }}</div>
        @else
            <div>{{ \LibreNMS\Util\Rewrite::normalizeIfType($port->ifType) }}</div>
        @endif

    </td>
    <td>
        <div>{{ $port->ifPhysAddress }}</div>
        <div>{{ $port->ifMtu ? __('port.mtu_label', ['mtu' => $port->ifMtu]) : '' }}</div>
    </td>
    <td @if($collapsing)class="tw:hidden tw:md:table-cell"@endif>
        <x-expandable height="5.8em">
            @foreach($data['neighbors'][$port->port_id] as $port_id => $neighbor)
                <div>
                    @php
                        $np = $data['neighbor_ports']?->get($neighbor['port_id']) ?? \App\Models\Port::find($neighbor['port_id']);
                    @endphp
                    @if($np)
                        @if(isset($neighbor['link']))
                            <i class="fa fa-link" aria-hidden="true"></i>
                        @elseif(isset($neighbor['pseudowire']))
                            <i class="fa fa-arrows-left-right" aria-hidden="true"></i>
                        @elseif(isset($neighbor['stack_parent']))
                            <i class="fa fa-expand" aria-hidden="true"></i>
                        @elseif(isset($neighbor['stack_child']))
                            <i class="fa fa-compress" aria-hidden="true"></i>
                        @elseif(isset($neighbor['pagp']))
                            <i class="fa fa-cube tw:text-green-600" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        @endif

                        <x-port-link :port="$np"></x-port-link>
                        on
                        <x-device-link :device="$np->device"></x-device-link>

                        @isset($neighbor['ipv6_network'])
                            <b class="tw:text-red-700">v6</b>
                        @endisset
                        @isset($neighbor['ipv4_network'])
                            <b class="tw:text-green-600">v4</b>
                        @endisset
                    @endif
                </div>
            @endforeach
        </x-expandable>
    </td>
</tr>
