<?php

$storage_array = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES:NetWare-Host-Ext-MIB');

if (is_array($storage_array)) {
    echo 'hrStorage : ';
    foreach ($storage_array as $index => $storage) {
        $fstype = $storage['hrStorageType'];
        $descr = $storage['hrStorageDescr'];
        $size = ($storage['hrStorageSize'] * $storage['hrStorageAllocationUnits']);
        $used = ($storage['hrStorageUsed'] * $storage['hrStorageAllocationUnits']);
        $units = $storage['hrStorageAllocationUnits'];
        $deny = 1;
        $perc_warn = 90;

        switch ($fstype) {
            case 'hrStorageVirtualMemory':
            case 'hrStorageRam':
            case 'nwhrStorageDOSMemory':
            case 'nwhrStorageMemoryAlloc':
            case 'nwhrStorageMemoryPermanent':
            case 'nwhrStorageCacheBuffers':
            case 'nwhrStorageCacheMovable':
            case 'nwhrStorageCacheNonMovable':
            case 'nwhrStorageCodeAndDataMemory':
            case 'nwhrStorageIOEngineMemory':
            case 'nwhrStorageMSEngineMemory':
            case 'nwhrStorageUnclaimedMemory':
                $deny = 0;
                break;
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

        if ($device['os'] == 'linux' || $device['os'] == 'vmware') {
            if ($descr == 'Physical memory' || $descr == 'Real Memory') {
                $perc_warn = 99;
                $deny = 0;
            } elseif ($descr == 'Virtual memory') {
                $perc_warn = 95;
            } elseif ($descr == 'Swap space') {
                $perc_warn = 10;
            }
        }

        if (! $deny && is_numeric($index)) {
            discover_mempool($valid_mempool, $device, $index, 'hrstorage', $descr, $units, null, null, $perc_warn);
        }

        unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $perc_warn);
    }//end foreach

    unset($storage_array);
}//end if
