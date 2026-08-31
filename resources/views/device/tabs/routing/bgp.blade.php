<div class="panel panel-default">
    <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <div>
            <strong>{{ __('Local AS') }}: {{ $data['local_as'] ?? __('N/A') }}</strong>
        </div>
        @php
            $views = [
                'basic' => __('Basic'),
                'updates' => __('Updates'),
                'prefixes_ipv4unicast' => __('IPv4 Ucast'),
                'prefixes_ipv4vpn' => __('VPNv4 Ucast'),
                'prefixes_ipv6unicast' => __('IPv6 Ucast'),
                'prefixes_ipv6vpn' => __('VPNv6 Ucast'),
                'macaccounting_bits' => __('Bits'),
                'macaccounting_pkts' => __('Packets'),
            ];
        @endphp
        <ul class="nav nav-pills" style="margin: 0;">
            @foreach($views as $viewKey => $viewLabel)
                <li class="{{ $data['view'] === $viewKey ? 'active' : '' }}">
                    <a href="{{ route('device', ['device' => $device, 'tab' => 'routing', 'vars' => 'proto=bgp/view=' . $viewKey]) }}">
                        {{ $viewLabel }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-hover table-condensed table-striped">
                <thead>
                    <tr>
                        <th>{{ __('Peer Address') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Family') }}</th>
                        <th>{{ __('Remote AS') }}</th>
                        <th>{{ __('Peer Description') }}</th>
                        <th>{{ __('Admin / State') }}</th>
                        <th>{{ __('Last Error') }}</th>
                        <th>{{ __('Uptime / Updates') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data['peers'] as $peerData)
                    @php
                        $peer = $peerData['peer'];
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('device', ['device' => $device, 'tab' => 'routing', 'vars' => 'proto=bgp/view=updates']) }}" class="tw:font-bold">
                                {{ $peerData['identifier_compressed'] }}
                            </a>
                            @if($peerData['linked_port'])
                                <br>
                                <x-device-link :device="$peerData['linked_port']->device" tab="routing" vars="proto=bgp" />
                                <x-port-link :port="$peerData['linked_port']" />
                            @endif
                        </td>
                        <td>
                            <span class="{{ $peerData['peer_type_class'] }} tw:font-semibold">{{ $peerData['peer_type'] }}</span>
                        </td>
                        <td style="font-size: 11px;">
                            {{ $peerData['afi_list'] ?: '-' }}
                        </td>
                        <td>
                            <strong>AS{{ $peer->bgpPeerRemoteAs }}</strong>
                            @if($peer->astext)
                                <br><small class="text-muted">{{ $peer->astext }}</small>
                            @endif
                        </td>
                        <td>{{ $peer->bgpPeerDescr ?: '-' }}</td>
                        <td>
                            <span class="label label-{{ $peerData['admin_color'] }}">{{ $peer->bgpPeerAdminStatus }}</span>
                            <br>
                            <span class="label label-{{ $peerData['state_color'] }}" style="margin-top: 4px; display: inline-block;">{{ $peer->bgpPeerState }}</span>
                        </td>
                        <td style="font-size: 11px;">
                            {!! nl2br(e($peerData['last_error'])) ?: '-' !!}
                        </td>
                        <td>
                            {{ \LibreNMS\Util\Time::formatInterval($peer->bgpPeerFsmEstablishedTime) }}
                            <br>
                            <small class="text-muted">
                                <i class="fa fa-arrow-down text-success" aria-hidden="true"></i> {{ $peer->bgpPeerInUpdates }}
                                <i class="fa fa-arrow-up text-primary" aria-hidden="true"></i> {{ $peer->bgpPeerOutUpdates }}
                            </small>
                        </td>
                    </tr>
                    @if($peerData['show_graph'])
                        <tr>
                            <td colspan="8" style="background: #fdfdfd; padding: 15px;">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <x-graph
                                            :device="$device"
                                            :type="$peerData['graph_type']"
                                            :id="$peerData['graph_id']"
                                            :height="120"
                                        />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px;">
                            <em>{{ __('No BGP peers found for this device.') }}</em>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
