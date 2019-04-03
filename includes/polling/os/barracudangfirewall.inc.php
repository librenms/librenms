<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

if ($device['sysObjectID'] == '.1.3.6.1.4.1.10704.1.10') {
    $hardware = $device['sysName'];
}

$sessions = snmp_get($device, 'firewallSessions64.8.102.119.83.116.97.116.115.0', '-OQv', 'PHION-MIB');

if (is_numeric($sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('fw_sessions', 'GAUGE', 0);

    $fields = array(
        'fw_sessions' => $sessions
    );

    $tags = compact('rrd_def');
    data_update($device, 'barracuda_firewall_sessions', $tags, $fields);
    $graphs['barracuda_firewall_sessions'] = true;
}


$packet_throughput = snmp_get($device, 'packetThroughput64.8.102.119.83.116.97.116.115.0', '-OQv', 'PHION-MIB');

if (is_numeric($packet_throughput)) {
    $rrd_def = RrdDefinition::make()->addDataset('packet_throughput', 'DERIVE', 0, 12500000000);

    $fields = array(
        'packet_throughput' => $packet_throughput
    );

    $tags = compact('rrd_def');
    data_update($device, 'barracuda_packet_throughput', $tags, $fields);
    $graphs['barracuda_packet_throughput'] = true;
}


$data_throughput = snmp_get($device, 'dataThroughput64.8.102.119.83.116.97.116.115.0', '-OQv', 'PHION-MIB');

if (is_numeric($data_throughput)) {
    $rrd_def = RrdDefinition::make()->addDataset('data_throughput', 'DERIVE', 0, 12500000000);

    $fields = array(
        'data_throughput' => $data_throughput
    );

    $tags = compact('rrd_def');
    data_update($device, 'barracuda_data_throughput', $tags, $fields);
    $graphs['barracuda_data_throughput'] = true;
}
