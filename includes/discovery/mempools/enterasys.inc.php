<?php
/*
 * LibreNMS Enterasys memory information module
 *
 * Copyright (c) 2017 Dave Bell <me@geordish.org>
 * Copyright (c) 2017 Neil Lathwood <gh+n@laf.io>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'enterasys' || $device['os'] == 'ewc') {
    $enterasys_mem = snmpwalk_cache_threepart_oid($device, 'etsysResourceStorageTable', array(), 'ENTERASYS-RESOURCE-UTILIZATION-MIB');
    foreach ($enterasys_mem as $index => $mem_data) {
        foreach ($mem_data['ram'] as $mem_id => $ram) {
            $free = $ram['etsysResourceStorageAvailable'];
            $total = $ram['etsysResourceStorageSize'];
            $descr = $ram['etsysResourceStorageDescr'];
            if ($index > 1000) {
                $descr = "Slot #" . substr($index, -1) . " $descr";
            }
            if (is_numeric($free) && is_numeric($total)) {
                discover_mempool($valid_mempool, $device, $index, 'enterasys', $descr, '1', $mem_id, null);
            }
        }
    }
}
