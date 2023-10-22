<?php

if ($device['os'] == 'truenas') {
    $zpooltable_array = snmpwalk_cache_oid($device, 'zpoolTable', null, 'FREENAS-MIB');

    if (is_array($zpooltable_array)) {
        foreach ($zpooltable_array as $index => $zpool) {
            if (isset($zpool['zpoolDescr'])) {
                $units = (int) $zpool['zpoolAllocationUnits'];

                discover_storage($valid_storage, $device,
                    $index,
                    'zpool',
                    'freenas-zpool',
                    $zpool['zpoolDescr'],
                    $zpool['zpoolSize'] * $units,
                    $units,
                    $zpool['zpoolUsed'] * $units,
                );
            }
        }
    }
}
