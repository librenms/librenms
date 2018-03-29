<?php

// Ignore this discovery module if we have already discovered things in CISCO-ENHANCED-MEMPOOL-MIB. Dirty duplication.
$cemp_count = dbFetchCell("SELECT COUNT(*) FROM `mempools` WHERE `device_id` = ? AND `mempool_type` = 'cemp'", array($device['device_id']));

if (($device['os_group'] == 'cisco') && $cemp_count == '0') {
    echo 'OLD-CISCO-MEMORY-POOL: ';

    $cmp_array = snmpwalk_cache_oid($device, 'ciscoMemoryPool', null, 'CISCO-MEMORY-POOL-MIB');

    if (is_array($cmp_array)) {
        foreach ($cmp_array as $index => $cmp) {
            if (is_numeric($cmp['ciscoMemoryPoolUsed']) && is_numeric($index)) {
                discover_mempool($valid_mempool, $device, $index, 'cmp', $cmp['ciscoMemoryPoolName'], '1', null, null);
            }
        }
    }
    unset($cmp_array);
}
