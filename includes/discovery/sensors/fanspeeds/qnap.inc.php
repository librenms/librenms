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

if ($device['os'] == 'qnap') {
    echo 'QNAP: ';

    $fan_descr_oid = '.1.3.6.1.4.1.24681.1.2.15.1.2';
    $fan_speed_oid = '.1.3.6.1.4.1.24681.1.2.15.1.3';
    $descr_oid = '24681.1.2.15.1.2.';
    $speed_oid = '24681.1.2.15.1.3.';


    $fans_descr = snmpwalk_cache_multi_oid($device, $fan_descr_oid, array());
    $fans_speed = snmpwalk_cache_multi_oid($device, $fan_speed_oid, array());

    if (is_array($fans_speed) && !empty($fans_speed)) {
        foreach ($fans_speed as $index => $entry) {
            $index = str_replace($speed_oid, '', $index);
            $fan_speed = $entry['enterprises'];
            $fan_serial = str_replace('"', '', $fans_descr[$descr_oid . $index]['enterprises']);
            discover_sensor($valid['sensor'], 'fanspeed', $device, $fan_speed_oid . '.' . $index, $index, 'snmp', $fan_serial, '1', '1', null, null, null, null, $fan_speed);
        }
    }
}
