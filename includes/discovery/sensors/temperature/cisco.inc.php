<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$temp = snmpwalk_cache_multi_oid($device, 'ciscoEnvMonTemperatureStatusTable', [], 'CISCO-ENVMON-MIB');
if (is_array($temp)) {
    $cur_oid = '.1.3.6.1.4.1.9.9.13.1.3.1.3.';
    foreach ($temp as $index => $entry) {
        if ($temp[$index]['ciscoEnvMonTemperatureState'] != 'notPresent' && ! empty($temp[$index]['ciscoEnvMonTemperatureStatusDescr'])) {
            $descr = ucwords($temp[$index]['ciscoEnvMonTemperatureStatusDescr']);
            discover_sensor($valid['sensor'], 'temperature', $device, $cur_oid . $index, $index, 'cisco', $descr, '1', '1', null, null, null, $temp[$index]['ciscoEnvMonTemperatureThreshold'], $temp[$index]['ciscoEnvMonTemperatureStatusValue'], 'snmp', $index);
        }
    }
}

$temp = snmpwalk_cache_multi_oid($device, 'c3gModemTemperature', [], 'CISCO-WAN-3G-MIB');
if (is_array($temp)) {
    $cur_oid = '.1.3.6.1.4.1.9.9.661.1.1.1.12.';
    foreach ($temp as $index => $entry) {
        $descr = snmp_get($device, 'entPhysicalName.' . $index, '-Oqv', 'ENTITY-MIB');
        discover_sensor($valid['sensor'], 'temperature', $device, $cur_oid . $index, $index, 'cisco', $descr, '1', '1', null, null, null, $temp[$index]['ciscoEnvMonTemperatureThreshold'], $temp[$index]['c3gModemTemperature'], 'snmp', $index);
    }
}
