<?php

$storage_array = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES:NetWare-Host-Ext-MIB');

if (is_array($storage_array)) {
    echo 'hrStorage : ';
    foreach ($storage_array as $index => $storage) {
        $fstype = $storage['hrStorageType'];
        $descr  = $storage['hrStorageDescr'];
        $size   = ($storage['hrStorageSize'] * $storage['hrStorageAllocationUnits']);
        $used   = ($storage['hrStorageUsed'] * $storage['hrStorageAllocationUnits']);
        $units  = $storage['hrStorageAllocationUnits'];
        $deny   = 1;

        switch ($fstype) {
            case 'hrStorageVirtualMemory':
            case 'hrStorageRam';
            case 'nwhrStorageDOSMemory';
            case 'nwhrStorageMemoryAlloc';
            case 'nwhrStorageMemoryPermanent';
            case 'nwhrStorageMemoryAlloc';
            case 'nwhrStorageCacheBuffers';
            case 'nwhrStorageCacheMovable';
            case 'nwhrStorageCacheNonMovable';
            case 'nwhrStorageCodeAndDataMemory';
            case 'nwhrStorageDOSMemory';
            case 'nwhrStorageIOEngineMemory';
            case 'nwhrStorageMSEngineMemory';
            case 'nwhrStorageUnclaimedMemory';
                $deny = 0;
            break;
        }

        if ($device['os'] == 'vmware' && $descr == 'Real Memory') {
            $deny = 0;
        }

        if ($device['os'] == 'routeros' && $descr == 'main memory') {
            $deny = 0;
        }

        if (strstr($descr, 'MALLOC') || strstr($descr, 'UMA')) {
            $deny = 1;
        } //end if
        if (strstr($descr, 'procfs') || strstr($descr, '/proc')) {
            $deny = 1;
        } //end if

        if (!$deny && is_numeric($index)) {
            discover_mempool($valid_mempool, $device, $index, 'hrstorage', $descr, $units, null, null);
        }

        unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd);
    }//end foreach

    unset($storage_array);
}//end if
