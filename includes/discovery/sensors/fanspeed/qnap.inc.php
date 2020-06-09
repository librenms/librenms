<?php
/*
 * LibreNMS QNAP NAS Fanspeeds information module
 *
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'QNAP: ';

$fan_descr_oid = '.1.3.6.1.4.1.24681.1.2.15.1.2';
$fan_speed_oid = '.1.3.6.1.4.1.24681.1.2.15.1.3';

$fans_descr = snmpwalk_cache_numerical_oid($device, $fan_descr_oid, [], null, null, '-OQUsn');
$fans_speed = snmpwalk_cache_numerical_oid($device, $fan_speed_oid, [], null, null, '-OQUsn');

if (is_array($fans_speed) && !empty($fans_speed)) {
    foreach ($fans_speed as $index => $entry) {
        $oid = $fan_speed_oid . '.' . $index;
        $fan_oid = $fan_descr_oid . '.' . $index;
        $fan_speed = $entry[$oid];
        $fan_serial = $fans_descr[$index][$fan_oid];
        if ($fan_speed) {
            discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'snmp', $fan_serial, '1', '1', null, null, null, null, $fan_speed);
        }
    }
}

unset(
    $fans_descr,
    $fans_speed
);
