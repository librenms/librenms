<?php

use LibreNMS\Util\IPv6;

$no_refresh = true;
?>
<table id="port-nd" class="table table-condensed table-hover table-striped">
    <thead>
        <tr>
            <th data-column-id="mac_address" data-formatter="tooltip">MAC address</th>
            <th data-column-id="mac_oui" data-sortable="false" data-width="" data-visible="<?php echo \LibreNMS\Config::get('mac_oui.enabled') ? 'true' : 'false' ?>" data-formatter="tooltip">Vendor</th>
            <th data-column-id="ipv6_address" data-formatter="tooltip">IPv6 address</th>
            <th data-column-id="remote_device" data-sortable="false">Remote device</th>
            <th data-column-id="remote_interface" data-sortable="false">Remote interface</th>
        </tr>
    </thead>
    <tbody>
    <?php

    foreach($port->nd as $nd) {
        $vendor = \LibreNMS\Util\Mac::parse($nd->mac_address)->vendor();
        $ipv6 = IPv6::parse($nd->ipv6_address, true);
        $port = PortCache::getByIp($ipv6);
        $device = $port?->device;
        echo Blade::render('<tr><td>{{ $nd->mac_address }}</td><td>{{ $vendor }}</td><td>{{ $ipv6 }}</td><td><x-device-link :device="$device" /></td><td>@if($port)<x-port-link :port="$port" />@endif</td></tr>',
            ['nd' => $nd, 'vendor' => $vendor, 'ipv6' => $ipv6->compressed(), 'device' => $device, 'port' => $port]);
    }
    ?>
    </tbody>
</table>
