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
     
$high_limit = 55;
$low_limit = 0;
     
// rcChasFanAmbientTemperature
$tempsfan = snmp_walk($device, '.1.3.6.1.4.1.2272.1.4.7.1.1.3', '-Osqn');
foreach (explode("\n", $tempsfan) as $i => $t) {
    $t   = explode(' ', $t);
    $oid = $t[0];
    $val = $t[1];
    $var1 = "rcChasFanAmbientTemperature";
    $var2 =   'Fan '.($i + 1).' temperature';
    if (is_numeric($val) && $val > 0) {
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $var1.$i, $var1, $var2, '1', '1', $low_limit, $low_limit, $high_limit, $high_limit, $val);
    }
}
     
// rcSingleCpSystem
$tempsystem = snmp_walk($device, '.1.3.6.1.4.1.2272.1.212', '-Osqn');
foreach (explode("\n", $tempsystem) as $i => $t) {
    $t   = explode(' ', $t);
    $oid = $t[0];
    $val = $t[1];
    switch ($oid) {
        //rcSingleCpSystemCpuTemperature
        case ($oid == '.1.3.6.1.4.1.2272.1.212.1.0' && $t[1] !=0):
            $var1 = "rcSingleCpSystemCpuTemperature";
            $var2 = "CPU temperature";
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $var1.$i, $var1, $var2, '1', '1', $low_limit, $low_limit, $high_limit, $high_limit, $val);
            break;
        //rcSingleCpSystemMacTemperature
        case ($oid == '.1.3.6.1.4.1.2272.1.212.2.0' && $t[1] !=0):
            $var1 = "rcSingleCpSystemMacTemperature";
            $var2 = "MAC temperature";
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $var1.$i, $var1, $var2, '1', '1', $low_limit, $low_limit, $high_limit, $high_limit, $val);
            break;
        //rcSingleCpSystemPhy2Temperature
        case ($oid == '.1.3.6.1.4.1.2272.1.212.3.0' && $t[1] !=0):
            $var1 = "rcSingleCpSystemPhy2Temperature";
            $var2 = "Phy1 temperature";
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $var1.$i, $var1, $var2, '1', '1', $low_limit, $low_limit, $high_limit, $high_limit, $val);
            break;
        //rcSingleCpSystemPhy1Temperature
        case ($oid == '.1.3.6.1.4.1.2272.1.212.4.0' && $t[1] !=0):
            $var1 = "rcSingleCpSystemPhy1Temperature";
            $var2 = "Phy2 temperature";
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $var1.$i, $var1, $var2, '1', '1', $low_limit, $low_limit, $high_limit, $high_limit, $val);
            break;
        //rcSingleCpSystemMac2Temperature
        case ($oid == '.1.3.6.1.4.1.2272.1.212.5.0' && $t[1] !=0):
            $var1 = "rcSingleCpSystemMac2Temperature";
            $var2 = "MAC2 temperature";
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $var1.$i, $var1, $var2, '1', '1', $low_limit, $low_limit, $high_limit, $high_limit, $val);
            break;
        default:
            $var1 = "Unknown";
    }
}
