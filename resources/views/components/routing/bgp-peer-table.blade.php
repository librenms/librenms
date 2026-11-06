<x-table striped :rows="$rows" :empty="__('No BGP peers found.')">
    <x-slot:head>
        <tr>
            <th>{{ __('Local Address') }}</th>
            <th>{{ __('Peer Address') }}</th>
            @if($showVrf)
                <th>{{ __('VRF') }}</th>
            @endif
            <th>{{ __('Type') }}</th>
            <th>{{ __('Family') }}</th>
            <th>{{ __('Remote AS') }}</th>
            <th>{{ __('Peer Description') }}</th>
            <th>{{ __('Admin / State') }}</th>
            <th>{{ __('Last Error') }}</th>
            <th>{{ __('Uptime / Updates') }}</th>
            @if($showPrefixes)
                <th>{{ __('Prefixes / Limit') }}</th>
            @endif
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach($rows as $row)
            <tr>
                <td>
                    @if($showDevice)
                        <a href="{{ route('device.routing.bgp', ['device' => $row['peer']->device_id, 'view' => 'updates']) }}" class="tw:font-bold">{{ $row['local_address'] ?: '-' }}</a>
                        <br>
                        <x-device-link :device="$row['peer']->device" tab="routing" section="bgp" />
                    @else
                        {{ $row['local_address'] ?: '-' }}
                    @endif
                    @if($row['local_port'])
                        <br>
                        <x-port-link :port="$row['local_port']" />
                    @endif
                </td>
                <td>
                    <a href="{{ route('device.routing.bgp', ['device' => $row['peer']->device_id, 'view' => 'updates']) }}" class="tw:font-bold">{{ $row['identifier'] }}</a>
                    @if($row['linked_port'])
                        <br>
                        <x-device-link :device="$row['linked_port']->device" tab="routing" section="bgp" />
                        <x-port-link :port="$row['linked_port']" />
                    @endif
                </td>
                @if($showVrf)
                    <td>{{ $row['vrf'] ?: '-' }}</td>
                @endif
                <td><span class="{{ $row['peer_type_class'] }} tw:font-semibold">{{ $row['peer_type'] }}</span></td>
                <td class="tw:text-[11px]">{{ $row['afi_list'] ?: '-' }}</td>
                <td>
                    <strong>AS{{ $row['peer']->bgpPeerRemoteAs }}</strong>
                    @if($row['peer']->astext)
                        <br><small class="text-muted">{{ $row['peer']->astext }}</small>
                    @endif
                </td>
                <td>{{ $row['peer']->bgpPeerDescr ?: '-' }}</td>
                <td>
                    <span class="label label-{{ $row['admin_color'] }}">{{ $row['peer']->bgpPeerAdminStatus }}</span>
                    <br>
                    <span class="label label-{{ $row['state_color'] }} tw:mt-1 tw:inline-block">{{ $row['peer']->bgpPeerState }}</span>
                </td>
                <td class="tw:text-[11px]">{{ $row['last_error'] ?: '-' }}</td>
                <td>
                    {{ $row['uptime'] }}
                    <br>
                    <small class="text-muted">
                        <i class="fa fa-arrow-down text-success" aria-hidden="true"></i> {{ $row['in_updates'] }}
                        <i class="fa fa-arrow-up text-primary" aria-hidden="true"></i> {{ $row['out_updates'] }}
                    </small>
                </td>
                @if($showPrefixes)
                    <td class="tw:text-[11px] tw:whitespace-nowrap">
                        @forelse($row['prefixes'] as $prefix)
                            <span class="{{ $prefix['class'] }}">
                                {{ $prefix['afisafi'] }}: {{ number_format($prefix['accepted']) }}
                                @if($prefix['limit'])
                                    / {{ number_format($prefix['limit']) }} ({{ $prefix['percent'] }}%)
                                @endif
                            </span>
                            @if(! $loop->last)
                                <br>
                            @endif
                        @empty
                            -
                        @endforelse
                    </td>
                @endif
            </tr>
            @if($row['graph_type'])
                <tr>
                    <td colspan="{{ $columnCount }}" class="tw:p-4">
                        <x-graph-row columns="4"
                            :device="$row['peer']->device"
                            :type="$row['graph_type']"
                            :height="120"
                            :vars="['id' => $row['graph_id']]"
                        />
                    </td>
                </tr>
            @endif
        @endforeach
    </x-slot:body>
</x-table>
