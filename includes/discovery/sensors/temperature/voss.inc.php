<?php
/**
 * voss.inc.php
 *
 * LibreNMS Temperature Sensor Discovery module for Extreme/Avaya VOSS(VSP Operating System Software)
 *
 * Copyright (c) 2017 Daniel Cox <danielcoxman@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$rcChasFan = snmpwalk_cache_multi_oid($device, 'rcChasFanAmbientTemperature', [], 'RAPID-CITY');
if (is_array($rcChasFan)) {
    foreach (array_keys($rcChasFan) as $index) {
        $descr = 'VOSS Fan ' . $index . ' temperature';
        $value = $rcChasFan[$index]['rcChasFanAmbientTemperature'];
        $var1 = 'rcChasFanAmbientTemperature';
        $oid = '.1.3.6.1.4.1.2272.1.4.7.1.1.3.' . $index;
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$var1.$index", 'avaya-vsp', $descr, '1', '1', null, null, null, null, $value);
    }
}

unset($rcChasFan);

$index = 'rcSingleCpSystemCpuTemperature.0';
$oid = '.1.3.6.1.4.1.2272.1.212.1.0';
$descr = 'VOSS CPU temperature';
$value = snmp_get($device, $index, '-OvqU', 'RAPID-CITY');
if ((is_numeric($value) && $value != 0)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'avaya-vsp', $descr, '1', '1', null, null, null, null, $value);
}

$index = 'rcSingleCpSystemMacTemperature.0';
$oid = '.1.3.6.1.4.1.2272.1.212.2.0';
$descr = 'VOSS MAC temperature';
$value = snmp_get($device, $index, '-OvqU', 'RAPID-CITY');
if ((is_numeric($value) && $value != 0)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'avaya-vsp', $descr, '1', '1', null, null, null, null, $value);
}

$index = 'rcSingleCpSystemPhy1Temperature.0';
$oid = '.1.3.6.1.4.1.2272.1.212.3.0';
$descr = 'VOSS PHY1 temperature';
$value = snmp_get($device, $index, '-OvqU', 'RAPID-CITY');
if ((is_numeric($value) && $value != 0)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'avaya-vsp', $descr, '1', '1', null, null, null, null, $value);
}

$index = 'rcSingleCpSystemPhy2Temperature.0';
$oid = '.1.3.6.1.4.1.2272.1.212.4.0';
$descr = 'VOSS PHY2 temperature';
$value = snmp_get($device, $index, '-OvqU', 'RAPID-CITY');
d_echo("VOSS $var1: $value\n");
if ((is_numeric($value) && $value != 0)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'avaya-vsp', $descr, '1', '1', null, null, null, null, $value);
}

$index = 'rcSingleCpSystemMac2Temperature.0';
$oid = '.1.3.6.1.4.1.2272.1.212.5.0';
$descr = 'VOSS MAC2 temperature';
$value = snmp_get($device, $index, '-OvqU', 'RAPID-CITY');
d_echo("VOSS $var1: $value\n");
if ((is_numeric($value) && $value != 0)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'avaya-vsp', $descr, '1', '1', null, null, null, null, $value);
}
