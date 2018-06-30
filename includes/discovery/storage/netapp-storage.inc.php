<?php

use LibreNMS\Config;

if ($device['os'] == 'netapp') {
    $netapp_storage = snmpwalk_cache_oid($device, 'dfEntry', null, 'NETAPP-MIB');

    if (is_array($netapp_storage)) {
        echo 'dfEntry ';
        foreach ($netapp_storage as $index => $storage) {
            $fstype = $storage['dfType'];
            $descr = $storage['dfFileSys'];
            $units = 1024;
            if (isset($storage['df64TotalKBytes']) && is_numeric($storage['df64TotalKBytes'])) {
                $size = ($storage['df64TotalKBytes'] * $units);
                $used = ($storage['df64UsedKBytes'] * $units);
            } else {
                $size = ($storage['dfKBytesTotal'] * $units);
                $used = ($storage['dfKBytesUsed'] * $units);
            }

            if (is_numeric($index)) {
                discover_storage($valid_storage, $device, $index, $fstype, 'netapp-storage', $descr, $size, $units, $used);
            }

            unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
        }
    }
}
