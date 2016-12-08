<?php

$hrstorage_array = snmpwalk_cache_oid($device, 'hrStorageEntry', null, 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES:NetWare-Host-Ext-MIB');

if (is_array($hrstorage_array)) {
    echo 'hrStorage : ';
    foreach ($hrstorage_array as $index => $storage) {
        $fstype                   = $storage['hrStorageType'];
        $descr                    = $storage['hrStorageDescr'];
        $storage['hrStorageSize'] = fix_integer_value($storage['hrStorageSize']);
        $storage['hrStorageUsed'] = fix_integer_value($storage['hrStorageUsed']);
        $size  = ($storage['hrStorageSize'] * $storage['hrStorageAllocationUnits']);
        $used  = ($storage['hrStorageUsed'] * $storage['hrStorageAllocationUnits']);
        $units = $storage['hrStorageAllocationUnits'];

        switch ($fstype) {
            case 'hrStorageVirtualMemory':
            case 'hrStorageRam':
            case 'hrStorageOther':
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
                $deny = 1;
                break;
        }

        if ($device['os'] == 'vmware' && $descr == 'Real Memory') {
            $old_rrdfile = array('storage', 'hrstorage', $descr);
            $new_rrdfile = array('mempool', 'hrstorage', $storage['hrStorageIndex']);
            rrd_file_rename($device, $old_rrdfile, $new_rrdfile);
            $deny = 1;
        }

        if ($deny != 1) {
            $deny = ignore_storage($descr);
        }

        if (isset($config['ignore_mount_removable']) && $config['ignore_mount_removable'] && $fstype == 'hrStorageRemovableDisk') {
            $deny = 1;
            d_echo("skip(removable)\n");
        }

        if (isset($config['ignore_mount_network']) && $config['ignore_mount_network'] && $fstype == 'hrStorageNetworkDisk') {
            $deny = 1;
            d_echo("skip(network)\n");
        }

        if (isset($config['ignore_mount_optical']) && $config['ignore_mount_optical'] && $fstype == 'hrStorageCompactDisc') {
            $deny = 1;
            d_echo("skip(cd)\n");
        }

        if (!$deny && is_numeric($index)) {
            discover_storage($valid_storage, $device, $index, $fstype, 'hrstorage', $descr, $size, $units, $used);
        }

        unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
    }//end foreach
}//end if
