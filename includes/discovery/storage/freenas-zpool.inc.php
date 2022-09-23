<?php

$zpooltable_array = snmpwalk_cache_oid($device, 'zpoolTable', null, 'FREENAS-MIB');

$sql = "SELECT `storage_descr` FROM `storage` WHERE `device_id`  = '" . $device['device_id'] . "' AND `storage_type` != 'zpool'";
$tmp_storage = dbFetchColumn($sql);

if (is_array($zpooltable_array)) {
    foreach ($zpooltable_array as $index => $zpool) {
        if (isset($zpool['zpoolDescr'])) {
            if (! in_array($zpool['zpoolDescr'], $tmp_storage)) {
                $zpool['zpoolIndex'] = $index;
                $zpool['zpoolTotal'] = $zpool['zpoolSize'] * $zpool['zpoolAllocationUnits'];
                $zpool['zpoolAvail'] = ($zpool['zpoolAvailable'] * $zpool['zpoolAllocationUnits']);
                $zpool['zpoolUsed'] = $zpool['zpoolTotal'] - $zpool['zpoolAvail'];

                discover_storage($valid_storage, $device, $zpool['zpoolIndex'], 'zpool', 'freenas-zpool', $zpool['zpoolDescr'], $zpool['zpoolTotal'], $zpool['zpoolAllocationUnits'], $zpool['zpoolUsed']);
            }
        }
    }
}
