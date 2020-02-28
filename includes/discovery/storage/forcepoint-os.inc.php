<?php
/**
 * forcepoint-os.inc.php
 * LibreNMS storage discovery module for forcepoint
 */
if ($device['os'] == 'forcepoint-os') {
    $forcepoint_tmp = snmpwalk_cache_oid($device, 'fwDiskStatsEntry', array(), 'STONESOFT-FIREWALL-MIB');
    $fstype = "Forcepoint FixedDisk";
    foreach($forcepoint_tmp as $storage_index => $data) {
        $storage_descr = $data['fwMountPointName'];
        $storage_units = 1024;
        if(is_numeric($data['fwPartitionSize']) && is_numeric($data['fwPartitionUsed'])) {
            $storage_size = $data['fwPartitionSize'];
            $storage_used = $data['fwPartitionUsed'];
            discover_storage($valid_storage, $device, $storage_index, $fstype, 'forcepoint-os', $storage_descr, $storage_size, $storage_units, $storage_used);
        }
    }
    unset($forcepoint_tmp);
}
