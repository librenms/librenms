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

if ($device['os'] == 'comware') {
    
    $entphydata = dbFetchRows("SELECT `entPhysicalIndex`, `entPhysicalDescr` FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalDescr` REGEXP 'MODULE|SENSOR' ORDER BY `entPhysicalIndex`", array(
        $device['device_id']
    ));
    $tempdata   = snmpwalk_cache_multi_oid($device, 'hh3cEntityExtTemperature', array(), 'HH3C-ENTITY-EXT-MIB');
    
    if (!empty($entphydata)) {
        foreach ($entphydata as $index) {
            foreach ($tempdata as $tempindex => $value) {
                if ($index['entPhysicalIndex'] == $tempindex) {
                    $cur_oid = '.1.3.6.1.4.1.25506.2.6.1.1.1.1.12.';
                    discover_sensor($valid['sensor'], 'temperature', $device, $cur_oid . $tempindex, $tempindex, 'comware', $index['entPhysicalDescr'], '1', '1', null, null, null, null, $value['hh3cEntityExtTemperature'], 'snmp', $index);
                }
            }
            
        }
    }
}
