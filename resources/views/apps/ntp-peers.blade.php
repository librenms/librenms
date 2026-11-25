{{-- $peers: peers grouped by device id, $appDeviceIds: devices with the ntp app (keys) --}}
@php($columns = array_values(array_filter([$showDevice ? __('Device') : null, __('Peer'), __('Stratum'), __('Peer Reference'), __('Status')])))
<x-panel :title="__('NTP Peers')">
    <x-slot:table>
        <x-table :columns="$columns" :rows="$peers" :empty="__('No NTP peers found.')">
            <x-slot:body>
                @foreach($peers as $deviceId => $devicePeers)
                    @foreach($devicePeers as $peer)
                        <tr @class(['danger' => ($peer['status'] ?? null) == 2])>
                            @if($showDevice)
                                <td>
                                    @if(isset($appDeviceIds[$deviceId]))
                                        <x-device-link :device="$deviceId" tab="apps" :query="['app' => 'ntp']" />
                                    @else
                                        <x-device-link :device="$deviceId" />
                                    @endif
                                </td>
                            @endif
                            <td>{{ $peer['peer'] ?? '' }}:{{ $peer['port'] ?? '' }}</td>
                            <td>{{ $peer['stratum'] ?? '' }}</td>
                            <td>{{ $peer['peerref'] ?? '' }}</td>
                            <td>{{ ($peer['status'] ?? null) == 2 ? ($peer['error'] ?? '') : __('OK') }}</td>
                        </tr>
                        @if($graphType)
                            <tr>
                                <td colspan="{{ count($columns) }}">
                                    <x-graph-row :type="$graphType" :device="$deviceId" columns="responsive" loading="lazy" />
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            </x-slot:body>
        </x-table>
    </x-slot:table>
</x-panel>
