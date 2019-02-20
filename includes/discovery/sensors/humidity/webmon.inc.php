<?php
/*
 * LibreNMS Dantel Webmon humidity sensor
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
    $oid = '.1.3.6.1.4.1.994.3.4.7.18.1.66.1.0';
    $index = '1';
    
    $descr = $env[1]['pSlot1Description'][0];
    $value = $env[1]['pSlot1LiveRaw'][0];
    $lowlimit = $env[1]['pSlot1Thresh4'][0];
    $low_warn_limit = $env[1]['pSlot1Thresh3'][0];
    $warnlimit = $env[1]['pSlot1Thresh2'][0];
    $high_limit = $env[1]['pSlot1Thresh1'][0];

    discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, 'webmon', $descr, '1', '1', $lowlimit, $low_warn_limit, $warnlimit, $high_limit, $value, 'snmp', null, null, null, null);
}
