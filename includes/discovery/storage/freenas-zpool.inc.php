<?php

if ($device['os'] == 'truenas') {
    $zpooltable_array = snmpwalk_cache_oid($device, 'zpoolTable', null, 'FREENAS-MIB');

    if (is_array($zpooltable_array)) {
        foreach ($zpooltable_array as $index => $zpool) {
            // new devices return a status string at zpoolAllocationUnits (.1.3.6.1.4.1.50536.1.1.1.1.3) and no longer support zpool usage stats
            if (is_numeric($zpool['zpoolAllocationUnits'])) {
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
