<?php
use LibreNMS\Config;
$nimble_storage = snmpwalk_cache_oid($device, 'volEntry', null, 'NIMBLE-MIB');
if (is_array($nimble_storage)) {
    echo 'volEntry ';
    foreach ($nimble_storage as $index => $storage) {
        $units  = 1024*1024;
	$fstype = $storage['volOnline'];
        $descr  = $storage['volName'];
        $size = $storage['volSizeLow'] * $units;
        $used = $storage['volUsageLow'] * $units;
	if (is_numeric($index)) {
            discover_storage($valid_storage, $device, $index, $fstype, 'nimbleos', $descr, $size, $units, $used);
        }
	unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
    }
}
