<?php

use App\Facades\DeviceCache;
use App\Facades\PortCache;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Util\Clean;
use LibreNMS\Util\Rewrite;

if (is_numeric($vars['id']) && ($auth || port_permitted($vars['id']))) {
    $port = PortCache::get($vars['id']);
    $device = DeviceCache::get((int) $port['device_id']);

    if ($device === null) {
        throw new RrdGraphException('Device not found', 'No Device');
    }

    $graph_title = $device->shortDisplayName() . '::' . strtolower(Rewrite::shortenIfName($port->ifDescr));

    if (($port->ifAlias != '') && ($port->ifAlias != $port->ifDescr)) {
        $graph_title .= '::' . Clean::html($port->ifAlias, []);
    }

    $auth = true;

    $rrd_filename = get_port_rrdfile_path($device->hostname, $port->port_id);
}
