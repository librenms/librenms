<?php

use App\Models\Port;
use LibreNMS\Util\Clean;
use LibreNMS\Util\Rewrite;

if (is_numeric($vars['id']) && ($auth || port_permitted($vars['id']))) {
    $port = cleanPort(Port::find($vars['id']));
    $device = DeviceCache::get($device['device_id']);
    $title = generate_device_link($device);
    $title .= ' :: Port  ' . generate_port_link($port);

    $graph_title = $device->shortDisplayName() . '::' . strtolower(Rewrite::shortenIfName($port->ifDescr));

    if (($port->ifAlias != '') && ($port->ifAlias != $port->ifDescr)) {
        $title .= ', ' . Clean::html($port->ifAlias, []);
        $graph_title .= '::' . Clean::html($port->ifAlias, []);
    }

    $auth = true;

    $rrd_filename = get_port_rrdfile_path($device->hostname, $port->port_id);
}
