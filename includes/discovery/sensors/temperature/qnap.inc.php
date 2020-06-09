<?php
/*
 * LibreNMS QNAP NAS temperature information module
 *
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'QNAP: ';


$cpu_temperature_oid = '.1.3.6.1.4.1.24681.1.3.5.0';
$cpu_temperature = snmp_get($device, $cpu_temperature_oid, '-Oqv');
discover_sensor($valid['sensor'], 'temperature', $device, $cpu_temperature_oid, '98', 'snmp', 'CPU Temperature', '1', '1', null, null, null, null, $cpu_temperature);

$system_temperature_oid = '.1.3.6.1.4.1.24681.1.3.6.0';
$system_temperature = snmp_get($device, $system_temperature_oid, '-Oqv');
discover_sensor($valid['sensor'], 'temperature', $device, $system_temperature_oid, '99', 'snmp', 'System Temperature', '1', '1', null, null, null, null, $system_temperature);

$disk_temperature_oid = '.1.3.6.1.4.1.24681.1.2.11.1.3';
$disk_serial_oid = '.1.3.6.1.4.1.24681.1.2.11.1.5';

$hdd_temps = snmpwalk_cache_numerical_oid($device, $disk_temperature_oid, [], null, null, '-OQUsn');
$hdd_serials = snmpwalk_cache_numerical_oid($device, $disk_serial_oid, [], null, null, '-OQUsn');

if (is_array($hdd_temps) && !empty($hdd_temps)) {
    foreach ($hdd_temps as $index => $entry) {
        $oid = $disk_temperature_oid . '.' . $index;
        $disk_oid = $disk_serial_oid . '.' . $index;
        $disk_temperature   = $entry[$oid];
        $disk_serial = $hdd_serials[$index][$disk_oid];
        if ($disk_serial == '--') {
            $disk_descr = "HDD $index empty bay";
        } else {
            $disk_descr = "HDD $index $disk_serial";
        }
        if ($disk_temperature) {
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'snmp', $disk_descr, '1', '1', null, null, null, null, $disk_temperature);
        }
    }
}

unset(
    $hdd_temps,
    $hdd_serials
);
