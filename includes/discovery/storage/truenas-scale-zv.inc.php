<?php

if ($device['os'] == 'truenas-scale') {
    $zvolTable_array = snmpwalk_cache_oid($device, 'zvolTable', null, 'TRUENAS-MIB');

    if (is_array($zvolTable_array)) {
        foreach ($zvolTable_array as $index => $zvol) {
            $units = 0;
            discover_storage(
                $valid_storage,
                $device,
                $index,
                'zvol',
                'truenas-scale-zv',
                $zvol['zvolDescr'],
                $zvol['zvolAvailableBytes'],
                $units,
                $zvol['zvolUsedBytes'],
            );
        }
    }
}
