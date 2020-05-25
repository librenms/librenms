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
