<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

$oids = ['entPhysicalContainedIn.1', 'entPhysicalSoftwareRev.1', 'entPhysicalSerialNum.1', 'entPhysicalModelName.1'];
$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB');
if ($data[1]['entPhysicalContainedIn'] == '0') {
    if (! empty($data[1]['entPhysicalSoftwareRev'])) {
        $version = $data[1]['entPhysicalSoftwareRev'];
    }
    if (! empty($data[1]['entPhysicalModelName'])) {
        $hardware = $data[1]['entPhysicalModelName'];
    }
    if (! empty($data[1]['entPhysicalSerialNum'])) {
        $serial = $data[1]['entPhysicalSerialNum'];
    }
}

$connections = snmp_get($device, 'CISCO-WAN-OPTIMIZATION-MIB::cwoTfoStatsActiveOptConn.0', '-OQv');

if (is_numeric($connections)) {
    $rrd_def = RrdDefinition::make()->addDataset('connections', 'GAUGE', 0);

    $fields = [
        'connections' => $connections,
    ];

    $tags = compact('rrd_def');
    data_update($device, 'waas_cwotfostatsactiveoptconn', $tags, $fields);
    $os->enableGraph('waas_cwotfostatsactiveoptconn');
}
