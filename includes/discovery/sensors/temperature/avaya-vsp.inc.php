<?php
/**
 * avaya-vsp.inc.php
 *
 * LibreNMS Temperature Sensor Discovery module for Avaya VOSS(VSP Operating System Software)
 *
 * Copyright (c) 2017 Daniel Cox <danielcoxman@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

//rcChasFanAmbientTemperature
$rcChasFan = snmpwalk_cache_multi_oid($device, 'rcChasFanAmbientTemperature', array(), 'RAPID-CITY');

if (is_array($rcChasFan)) {

    foreach (array_keys($rcChasFan) as $index) {
        $descr = "Fan ".$index." temperature";
        $value = $rcChasFan[$index]['rcChasFanAmbientTemperature'];
        $var1 = 'rcChasFanAmbientTemperature';
        $oid = '.1.3.6.1.4.1.2272.1.4.7.1.1.3.'.$index;
        d_echo("VOSS $var1.$index: $value\n");

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$var1.$index", $var1, $descr, '1', '1', null, null, null, null, $value);
    }
}

//rcSingleCpSystemCpuTemperature
$var1 = "rcSingleCpSystemCpuTemperature";
$oid = ".1.3.6.1.4.1.2272.1.212.1.0";
$descr = "CPU temperature";
$value = snmp_get($device, "$var1.0", '-OvqU', 'RAPID-CITY');
d_echo("VOSS $var1: $value\n");
if ((is_numeric($value) && $value != 0)) {

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$var1.$index", $var1, $descr, '1', '1', null, null, null, null, $value);

}

//rcSingleCpSystemMacTemperature
$var1 = "rcSingleCpSystemMacTemperature";
$oid = ".1.3.6.1.4.1.2272.1.212.2.0";
$descr = "MAC temperature";
$value = snmp_get($device, "$var1.0", '-OvqU', 'RAPID-CITY');
d_echo("VOSS $var1: $value\n");
if ((is_numeric($value) && $value != 0)) {

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$var1.$index", $var1, $descr, '1', '1', null, null, null, null, $value);

}

//rcSingleCpSystemPhy1Temperature
$var1 = "rcSingleCpSystemPhy1Temperature";
$oid = ".1.3.6.1.4.1.2272.1.212.3.0";
$descr = "PHY1 temperature";
$value = snmp_get($device, "$var1.0", '-OvqU', 'RAPID-CITY');
d_echo("VOSS $var1: $value\n");
if ((is_numeric($value) && $value != 0)) {

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$var1.$index", $var1, $descr, '1', '1', null, null, null, null, $value);

}

//rcSingleCpSystemPhy2Temperature
$var1 = "rcSingleCpSystemPhy2Temperature";
$oid = ".1.3.6.1.4.1.2272.1.212.4.0";
$descr = "PHY2 temperature";
$value = snmp_get($device, "$var1.0", '-OvqU', 'RAPID-CITY');
d_echo("VOSS $var1: $value\n");
if ((is_numeric($value) && $value != 0)) {

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$var1.$index", $var1, $descr, '1', '1', null, null, null, null, $value);

}

//rcSingleCpSystemMac2Temperature
$var1 = "rcSingleCpSystemMac2Temperature";
$oid = ".1.3.6.1.4.1.2272.1.212.5.0";
$descr = "MAC2 temperature";
$value = snmp_get($device, "$var1.0", '-OvqU', 'RAPID-CITY');
d_echo("VOSS $var1: $value\n");
if ((is_numeric($value) && $value != 0)) {

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, "$var1.$index", $var1, $descr, '1', '1', null, null, null, null, $value);

}
