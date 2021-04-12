<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2016 Peter TKATCHENKO https://github.com/Peter2121/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'equalogic') {
    $eql_storage = snmpwalk_cache_oid($device, 'EqliscsiVolumeEntry', null, 'EQLVOLUME-MIB', 'equallogic');

    if (is_array($eql_storage)) {
        echo 'EqliscsiVolumeEntry ';
        foreach ($eql_storage as $index => $storage) {
            $fstype = $storage['eqliscsiVolumeAdminStatus'];
            $descr = $storage['eqliscsiVolumeName'];
            $units = 1000000;
            $size = $storage['eqliscsiVolumeSize'] * $units;
            $used = $storage['eqliscsiVolumeStatusAllocatedSpace'] * $units;
            if (is_int($index)) {
                discover_storage($valid_storage, $device, $index, $fstype, 'eql-storage', $descr, $size, $units, $used);
            } else {
                // Trying to search the last '.' and take something after it as index
                $arrindex = explode('.', $index);
                $newindex = (int) cast_number(end($arrindex));
                if (is_int($newindex)) {
                    discover_storage($valid_storage, $device, $newindex, $fstype, 'eql-storage', $descr, $size, $units, $used);
                }
            }
            unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
        }
    }
}
