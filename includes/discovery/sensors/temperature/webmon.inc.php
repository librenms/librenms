<?php

/*
 * LibreNMS Dantel Webmon temperature sensor
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Mike Williams
 * @author     Mike Williams <mike@mgww.net>
 */

$env = snmpwalk_group($device, 'pSlot1Table', 'WEBMON-EDGE-MATRIX-MIB');

if (!empty($env)) {
    $oid = '.1.3.6.1.4.1.994.3.4.7.18.1.66.2.0';
    $index = '2';
    
    $descr = $env[2]['pSlot1Description'][0];
    $value = $env[2]['pSlot1LiveRaw'][0];
    $lowlimit = $env[2]['pSlot1Thresh4'][0];
    $low_warn_limit = $env[2]['pSlot1Thresh3'][0];
    $warnlimit = $env[2]['pSlot1Thresh2'][0];
    $high_limit = $env[2]['pSlot1Thresh1'][0];
    $func = null;
    if ($env[2]['pSlot1Units'][0] == 'Fahrenheit') {
        $func = 'fahrenheit_to_celsius';
        $value = fahrenheit_to_celsius($value);
        $lowlimit = fahrenheit_to_celsius($lowlimit);
        $low_warn_limit = fahrenheit_to_celsius($low_warn_limit);
        $warnlimit = fahrenheit_to_celsius($warnlimit);
        $high_limit = fahrenheit_to_celsius($high_limit);
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'webmon', $descr, '1', '1', $lowlimit, $low_warn_limit, $warnlimit, $high_limit, $value, 'snmp', null, null, $func, null);
}
