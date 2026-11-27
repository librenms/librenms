<x-panel>
    <x-slot:slot class="tw:p-0!">
    <table id="ports-arp" class="table table-condensed table-hover table-striped tw:mt-1 tw:mb-0!">
        <thead>
        <tr>
            @empty($port)
            <th data-column-id="interface">Port</th>
            @endempty
            <th data-column-id="mac_address" data-formatter="tooltip">MAC address</th>
            @config('mac_oui.enabled')
            <th data-column-id="mac_oui" data-sortable="false" data-width="150px" data-visible="false" data-formatter="tooltip">Vendor</th>
            @endconfig
            <th data-column-id="ipv6_address" data-formatter="tooltip">IPv6 address</th>
            <th data-column-id="remote_device" data-sortable="false">Remote device</th>
            <th data-column-id="remote_interface" data-sortable="false">Remote interface</th>
        </tr>
        </thead>
        <tbody>
        @foreach(($port ?? DeviceCache::getPrimary())->nd as $nd)
            @php $remote = PortCache::getByIp($nd->ipv6_address); @endphp
        <tr>
            @empty($port)
            <td><x-port-link :port="PortCache::get($nd->port_id)" /></td>
            @endempty
            <td>{{ $nd->mac_address }}</td>
            @config('mac_oui.enabled')
            <td>{{ \LibreNMS\Util\Mac::parse($nd->mac_address)->vendor() }}</td>
            @endconfig
            <td>{{ \LibreNMS\Util\IPv6::parse($nd->ipv6_address, true)->compressed() }}</td>
            <td><x-device-link :device="$remote?->device" /></td>
            <td>@if($remote)<x-port-link :port="$remote" />@endif</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </x-slot:slot>
</x-panel>
