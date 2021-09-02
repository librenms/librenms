<?php

use Illuminate\Support\Str;
use LibreNMS\Config;

$hrstorage_array = $os->getCacheTable('hrStorageTable', 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');

if (is_array($hrstorage_array)) {
    echo 'hrStorage : ';

    $bad_fs_types = [
        'hrStorageVirtualMemory',
        'hrStorageRam',
        'hrStorageOther',
        'nwhrStorageDOSMemory',
        'nwhrStorageMemoryAlloc',
        'nwhrStorageMemoryPermanent',
        'nwhrStorageCacheBuffers',
        'nwhrStorageCacheMovable',
        'nwhrStorageCacheNonMovable',
        'nwhrStorageCodeAndDataMemory',
        'nwhrStorageIOEngineMemory',
        'nwhrStorageMSEngineMemory',
        'nwhrStorageUnclaimedMemory',
    ];

    foreach ($hrstorage_array as $index => $storage) {
        $fstype = $storage['hrStorageType'];
        $descr = $storage['hrStorageDescr'];
        $storage['hrStorageSize'] = fix_integer_value($storage['hrStorageSize']);
        $storage['hrStorageUsed'] = fix_integer_value($storage['hrStorageUsed']);
        $size = ($storage['hrStorageSize'] * $storage['hrStorageAllocationUnits']);
        $used = ($storage['hrStorageUsed'] * $storage['hrStorageAllocationUnits']);
        $units = $storage['hrStorageAllocationUnits'];

        if (in_array($fstype, $bad_fs_types)) {
            continue;
        }

        if (Str::startsWith($device['os'], 'vmware') && $descr == 'Real Memory') {
            $old_rrdfile = ['storage', 'hrstorage', $descr];
            $new_rrdfile = ['mempool', 'hrstorage', $storage['hrStorageIndex']];
            \Rrd::renameFile($device, $old_rrdfile, $new_rrdfile);
            continue;
        }

        // Skip hrStorage if aixFsTable is available
        if ($device['os'] == 'aix' && ! empty($aix_filesystem)) {
            continue;
        }

        if (ignore_storage($device['os'], $descr)) {
            continue;
        }

        if (Config::get('ignore_mount_removable', false) && $fstype == 'hrStorageRemovableDisk') {
            d_echo("skip(removable)\n");
            continue;
        }

        if (Config::get('ignore_mount_network', false) && $fstype == 'hrStorageNetworkDisk') {
            d_echo("skip(network)\n");
            continue;
        }

        if (Config::get('ignore_mount_optical', false) && $fstype == 'hrStorageCompactDisc') {
            d_echo("skip(cd)\n");
            continue;
        }

        if (is_numeric($index)) {
            discover_storage($valid_storage, $device, $index, $fstype, 'hrstorage', $descr, $size, $units, $used);
        }

        unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
    }//end foreach
}//end if
