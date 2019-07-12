<?php
/**
 * scality.inc.php
 */
use LibreNMS\Config;

if ($device['os'] === 'scality') {
    $scality_storage = snmpwalk_group($device, 'ringEntry', 'SCALITY-MIB', 1, array(), 'scality');
    $scality_storage = snmp_get_multi_oid($device, ['ringName.1', 'ringStorageTotal.1', 'ringStorageUsed.1'], null, 'SCALITY-MIB');
    if (is_numeric($scality_storage['ringStorageTotal.1'])) {
        $units = 1024;
        $fstype = "dsk";
        $index  = 0;
        $descr = $scality_storage['ringName.1'];
        $size = (($scality_storage['ringStorageTotal.1']) * $units);
        $used = (($scality_storage['ringStorageUsed.1']) * $units);
        echo ['ringName.1', 'ringStorageTotal.1', 'ringStorageUsed.1'];
        discover_storage($valid_storage, $device, $index, $fstype, 'scality', $descr, $size, $units, $used);
        }
}

