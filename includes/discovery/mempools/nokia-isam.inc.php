<?php

/*
 * LibreNMS Nokia ISAM RAM discovery module
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/

if ($device['os'] == 'nokia-isam') {
    echo 'Nokia ISAM Memory: ';

    $slotTable = [
    '4352' => 'acu:1/1/',
    '4353' => 'nt-a:',
    '4354' => 'nt-b:',
    '4355' => 'lt:1/1/1/',
    '4356' => 'lt:1/1/2/',
    '4357' => 'lt:1/1/3/',
    '4358' => 'lt:1/1/4/',
    '4359' => 'lt:1/1/5/',
    '4360' => 'lt:1/1/6/',
    '4361' => 'lt:1/1/7/',
    '4362' => 'lt:1/1/8/',
    '4481' => '4481' // FIXME define this
    ];

    $array = snmpwalk_cache_multi_oid($device, 'mem', null, 'ASAM-SYSTEM-MIB');

    if (is_array($array)) {
        foreach ($array as $index => $entry) {
            if (is_numeric($entry['memAbsoluteUsage']) && is_numeric($entry['totalMemSize'])) {
                list($entPhysicalIndex) = explode('.', $index);
                $entPhysicalName        = $entPhysicalIndex;
        
                $descr = $slotTable[$index].' Memory ('.$index.')';

                discover_mempool($valid_mempool, $device, $index, 'nokia-isam', $descr, '1', $entPhysicalIndex, null);
            }
        }
    }
}
