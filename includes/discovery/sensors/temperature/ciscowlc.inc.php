<?php
/*
 * LibreNMS Cisco wireless controller temperature monitoring module
 *
 * Copyright (c) 2016 Tuomas Riihimäki <tuomari@iudex.fi>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo ' AIRESPACE temperature ';

$temp = snmpwalk_cache_multi_oid($device, 'bsnSensorTemperature', [], 'AIRESPACE-WIRELESS-MIB');
$low = snmpwalk_cache_multi_oid($device, 'bsnTemperatureAlarmLowLimit', [], 'AIRESPACE-WIRELESS-MIB');
$high = snmpwalk_cache_multi_oid($device, 'bsnTemperatureAlarmHighLimit', [], 'AIRESPACE-WIRELESS-MIB');

if (is_array($temp)) {
    $cur_oid = '.1.3.6.1.4.1.14179.2.3.1.13.';
    foreach ($temp as $index => $entry) {
        $descr = 'Unit Temperature ' . $index;
        echo " $descr, ";
        discover_sensor($valid['sensor'], 'temperature', $device, $cur_oid . $index, $index, 'wlc', $descr, '1', '1', null, $low[$index]['bsnTemperatureAlarmLowLimit'], $high[$index]['bsnTemperatureAlarmHighLimit'], null, $temp[$index]['bsnSensorTemperature'], 'snmp', $index);
    }
}
