<?php

$ilo_storage = snmpwalk_cache_oid($device, 'cpqHoFileSysEntry', null, 'CPQHOST-MIB', 'hpe-ilo');

if (is_array($ilo_storage)) {
    echo 'HPE ILO ';
    foreach ($ilo_storage as $index => $storage) {
        $fstype = "Flash"; //$storage['eqliscsiVolumeAdminStatus'];
        $descr  = $storage['cpqHoFileSysDesc'];
        $units  = 1024*1024;
        $size = $storage['cpqHoFileSysSpaceTotal'];
        $used = $storage['cpqHoFileSysSpaceUsed'];
        if (is_int($index)) {
            discover_storage($valid_storage, $device, $index, $fstype, 'hpe-ilo', $descr, $size, $units, $used);
        //discover_storage($valid_storage, $device, $index, $fstype, 'ilo-storage', $descr, $size, $units, $used);
        }
        unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
    }
}
