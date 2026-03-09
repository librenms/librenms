<?php

use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Util\Rewrite;

if (is_numeric($vars['id']) && ($auth || port_permitted($vars['id']))) {
    $port = cleanPort(get_port_by_id($vars['id']));
    $device = device_by_id_cache($port['device_id']);

    if (empty($device['device_id'])) {
        throw new RrdGraphException('Device not found', 'No Device');
    }

    $title = generate_device_link($device);
    $title .= ' :: Port  ' . generate_port_link($port);

    $graph_title = DeviceCache::get($device['device_id'])->shortDisplayName() . '::' . strtolower(Rewrite::shortenIfName($port['ifDescr']));

    if (($port['ifAlias'] != '') && ($port['ifAlias'] != $port['ifDescr'])) {
        $title .= ', ' . \LibreNMS\Util\Clean::html($port['ifAlias'], []);
        $graph_title .= '::' . \LibreNMS\Util\Clean::html($port['ifAlias'], []);
    }

    $auth = true;

    $rrd_filename = get_port_rrdfile_path($device['hostname'], $port['port_id']);
}
