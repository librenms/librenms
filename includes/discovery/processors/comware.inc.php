<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'comware') {
    echo 'Comware ';

    $x = 1;
    $entphydata = dbFetchRows("SELECT `entPhysicalIndex` FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalName` LIKE '%Board%' ORDER BY `entPhysicalIndex`", array($device['device_id']));

    if (!empty($entphydata)) {
        $procdata = snmpwalk_cache_multi_oid($device, 'hh3cEntityExtCpuUsage', array(), 'HH3C-ENTITY-EXT-MIB');
        foreach ($entphydata as $index) {
            foreach ($procdata as $procindex => $value) {
                if ($index['entPhysicalIndex'] == $procindex) {
                    $cur_oid = '.1.3.6.1.4.1.25506.2.6.1.1.1.1.6.';
 
                    discover_processor($valid['processor'], $device, $cur_oid . $procindex, $procindex, 'comware', 'Slot ' . $x, '1', $value['h3cEntityExtCpuUsage']);
                    $x++;
                }
            }
        }
    }
}
