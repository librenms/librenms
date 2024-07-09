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
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
*/
echo 'Comware ';

$entphydata = DeviceCache::getPrimary()->entityPhysical()
    ->whereIn('entPhysicalClass', ['module', 'sensor'])
    ->orderBy('entPhysicalIndex')
    ->get(['entPhysicalIndex', 'entPhysicalClass', 'entPhysicalName'])
    ->toArray();

if (! empty($entphydata)) {
    $tempdata = snmpwalk_cache_multi_oid($device, 'hh3cEntityExtTemperature', [], 'HH3C-ENTITY-EXT-MIB');
    $tempdata = snmpwalk_cache_multi_oid($device, 'hh3cEntityExtTemperatureThreshold', $tempdata, 'HH3C-ENTITY-EXT-MIB');
    foreach ($entphydata as $index) {
        foreach ($tempdata as $tempindex => $value) {
            if ($index['entPhysicalIndex'] == $tempindex && $value['hh3cEntityExtTemperature'] != 65535) {
                if ($value['hh3cEntityExtTemperatureThreshold'] != 65535) {
                    $hightemp = $value['hh3cEntityExtTemperatureThreshold'];
                } else {
                    $hightemp = null;
                }
                $cur_oid = '.1.3.6.1.4.1.25506.2.6.1.1.1.1.12.';
                discover_sensor(
                    $valid['sensor'],
                    'temperature',
                    $device,
                    $cur_oid . $tempindex,
                    'temp-' . $tempindex,
                    'comware',
                    $index['entPhysicalName'],
                    '1',
                    '1',
                    null,
                    null,
                    null,
                    $hightemp,
                    $value['hh3cEntityExtTemperature'],
                    'snmp',
                    $index['entPhysicalIndex']
                );
            }
        }
    }
}
