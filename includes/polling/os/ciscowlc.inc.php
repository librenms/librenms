<?php
/*
 * LibreNMS Cisco wireless controller information module
 *
 * Copyright (c) 2016 Tuomas Riihimäki <tuomari@iudex.fi>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

global $config;

$oids = 'entPhysicalModelName.1 entPhysicalSoftwareRev.1 entPhysicalSerialNum.1';

$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB');

if (isset($data[1]['entPhysicalSoftwareRev']) && $data[1]['entPhysicalSoftwareRev'] != '') {
    $version = $data[1]['entPhysicalSoftwareRev'];
}

if (isset($data[1]['entPhysicalName']) && $data[1]['entPhysicalName'] != '') {
    $hardware = $data[1]['entPhysicalName'];
}

if (isset($data[1]['entPhysicalModelName']) && $data[1]['entPhysicalModelName'] != '') {
    $hardware = $data[1]['entPhysicalModelName'];
}

if (empty($hardware)) {
    $hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:CISCO-PRODUCTS-MIB');
}


$oids_AP_Name = array(
	'bsnAPName',
);

$oids_AP_Users = array(
	'bsnApIfNoOfUsers',
);

foreach ($oids_AP_Name as $oid) {
	$stats = snmpwalk_cache_oid($device, $oid, $stats, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsxb');
}

foreach ($oids_AP_Users as $oid) {
	$APstats = snmpwalk_cache_oid($device, $oid, $APstats, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsxb');
}

$numAccessPoints = count($stats);
$numClients = 0;

foreach ($APstats as $key => $value) {
	$numClients += $value['bsnApIfNoOfUsers'];
}

$rrd_def = array(
    'DS:NUMAPS:GAUGE:600:0:12500000000',
    'DS:NUMCLIENTS:GAUGE:600:0:12500000000'
);

$fields = array(
    'NUMAPS'     => $numAccessPoints,
    'NUMCLIENTS' => $numClients
);

$tags = compact('rrd_def');
data_update($device, 'ciscowlc', $tags, $fields);

// also save the info about how many clients in the same place as the wireless module
$radio = 1;
$rrd_name = 'wificlients-radio'.$radio;
$rrd_def = 'DS:wificlients:GAUGE:600:-273:10000';

$fields = array(
    'wificlients' => $numClients
);

$tags = compact('radio', 'rrd_name', 'rrd_def');
data_update($device, 'wificlients', $tags, $fields);

$graphs['wifi_clients'] = true;
