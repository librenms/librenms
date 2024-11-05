<?php
/*
 * This is called from LibreNMS/Modules/LegacyModule.php
 */

if ((isset($device)) && ($device['os'] == 'forcepoint')) {
    $forcepoint_fs=snmpwalk_cache_oid($device, 'fwDiskStatsTable', [], 'STONESOFT-FIREWALL-MIB');

    if (is_array($forcepoint_fs)) {
        foreach ($forcepoint_fs as $i => $partition) {
            echo 'Forcepoint filesystem ';
            discover_storage($valid_storage,
                $device,
                $i, 'fs',
                'forcepoint',
                $partition['fwMountPointName'],
                $partition['fwPartitionSize']*1024,
                1,
                $partition['fwPartitionUsed']*1024);
        }
    } else {
        print 'fwDiskStatsTable did not return an array.' . PHP_EOL;
    }
    unset($forcepoint_fs,$partition,$i);
}
