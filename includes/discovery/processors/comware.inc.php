<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * Copyright (c) 2017 Tony Murray <murraytony@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'comware') {
    echo 'HPE Comware ';

    $entity_data = snmpwalk_cache_oid($device, 'entPhysicalClass', array(), 'ENTITY-MIB');
    $indexes = array_keys(array_filter($entity_data, function ($entry) {
        return $entry['entPhysicalClass'] == 'module';
    }));

    if (!empty($indexes)) {
        $procdata = snmpwalk_cache_oid($device, 'hh3cEntityExtCpuUsage', array(), 'HH3C-ENTITY-EXT-MIB');

        foreach ($indexes as $i => $entIndex) {
            if (isset($procdata[$entIndex])) {
                $cur_oid = ".1.3.6.1.4.1.25506.2.6.1.1.1.1.6.$entIndex";
                $cur_value = $procdata[$entIndex]['hh3cEntityExtCpuUsage'];
                $descr = 'Slot ' . ++$i;
                if ($cur_value > 0) {
                    discover_processor($valid['processor'], $device, $cur_oid, $entIndex, 'comware', $descr, '1', $cur_value);
                }
            }
        }
    }
}
