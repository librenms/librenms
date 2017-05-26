<?php
/*
 * LibreNMS Cisco AsyncOS information module
 *
 * Copyright (c) 2017 Mike Williams <mike@mgww.net>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

list($hardware,$version,,$serial) = explode(',', $device['sysDescr']);

preg_match('/\w[\d]+\w?/', $hardware, $regexp_results);
$hardware = $regexp_results[0];

preg_match('/[\d\.-]+/', $version, $regexp_results);
$version = $regexp_results[0];

preg_match('/[[\w]+-[\w]+/', $serial, $regexp_results);
$serial = $regexp_results[0];

$sysobjectid = snmp_get($device, 'sysObjectID.0', '-OQnv', 'SNMPv2-MIB');

# Get stats only if device is web proxy
if (strcmp($sysobjectid, '.1.3.6.1.4.1.15497.1.2') == 0) {
    $connections = snmp_get($device, 'tcpCurrEstab.0', '-OQv', 'TCP-MIB');

    if (is_numeric($connections)) {
        $rrd_def = 'DS:connections:GAUGE:2000:0:U';

        $fields = array(
            'connections' => $connections,
        );

        $tags = compact('rrd_def');
        data_update($device, 'asyncos_conns', $tags, $fields);
        $graphs['asyncos_conns'] = true;
    }
}
