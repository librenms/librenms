<?php
/*
 * Copyright (c) 2019 David Leselidze <d.l@comcast.net>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$aix_filesystem = snmpwalk_cache_oid($device, 'aixFsTableEntry', [], 'IBM-AIX-MIB');

$sql = "SELECT `storage_descr` FROM `storage` WHERE `device_id`  = '" . $device['device_id'] . "' AND `storage_type` != 'aixFileSystem'";
$tmp_storage = dbFetchColumn($sql);

if (is_array($aix_filesystem)) {
    echo 'aix_filesystem : ';

    foreach ($aix_filesystem as $aix_fs) {
        if (isset($aix_fs['aixFsMountPoint'])) {
            if ($aix_fs['aixFsType'] == 'jfs' || $aix_fs['aixFsType'] == 'jfs2') { // Only JFS or JFS2
                if (! in_array($aix_fs['aixFsMountPoint'], $tmp_storage)) {
                    $aix_fs['aixFsSize'] = $aix_fs['aixFsSize'] * 1024 * 1024;
                    $aix_fs['aixFsFree'] = $aix_fs['aixFsFree'] * 1024 * 1024;
                    $aix_fs['aixFsUsed'] = $aix_fs['aixFsSize'] - $aix_fs['aixFsFree'];

                    discover_storage($valid_storage, $device, $aix_fs['aixFsIndex'], 'aixFileSystem', 'aix', $aix_fs['aixFsMountPoint'], $aix_fs['aixFsSize'], 1024 * 1024, $aix_fs['aixFsUsed']);
                }
            }
        }
        unset($tmp_storage, $aix_fs);
    } // end foreach
} // endif
