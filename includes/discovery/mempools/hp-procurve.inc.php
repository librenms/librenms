<?php

// NETSWITCH-MIB::hpLocalMemSlotIndex.1 = INTEGER: 1
// NETSWITCH-MIB::hpLocalMemSlabCnt.1 = Counter32: 3966
// NETSWITCH-MIB::hpLocalMemFreeSegCnt.1 = Counter32: 166
// NETSWITCH-MIB::hpLocalMemAllocSegCnt.1 = Counter32: 3803
// NETSWITCH-MIB::hpLocalMemTotalBytes.1 = INTEGER: 11337704
// NETSWITCH-MIB::hpLocalMemFreeBytes.1 = INTEGER: 9669100
// NETSWITCH-MIB::hpLocalMemAllocBytes.1 = INTEGER: 1668732
// NETSWITCH-MIB::hpGlobalMemSlotIndex.1 = INTEGER: 1
// NETSWITCH-MIB::hpGlobalMemSlabCnt.1 = Counter32: 3966
// NETSWITCH-MIB::hpGlobalMemFreeSegCnt.1 = Counter32: 166
// NETSWITCH-MIB::hpGlobalMemAllocSegCnt.1 = Counter32: 3803
// NETSWITCH-MIB::hpGlobalMemTotalBytes.1 = INTEGER: 11337704
// NETSWITCH-MIB::hpGlobalMemFreeBytes.1 = INTEGER: 9669104
// NETSWITCH-MIB::hpGlobalMemAllocBytes.1 = INTEGER: 1668728
$array = snmpwalk_cache_oid($device, 'hpLocal', null, 'NETSWITCH-MIB', 'hp');
$array = snmpwalk_cache_oid($device, 'hpGlobal', $array, 'NETSWITCH-MIB', 'hp');

if (is_array($array)) {
    echo 'Procurve : ';
    foreach ($array as $index => $mempool) {
        if (is_numeric($index) && is_numeric($mempool['hpLocalMemTotalBytes'])) {
            discover_mempool($valid_mempool, $device, $index, 'hpLocal', 'Local Memory '.$index, null, null, null);
        }

        if (is_numeric($index) && is_numeric($mempool['hpGlobalMemTotalBytes'])) {
            discover_mempool($valid_mempool, $device, $index, 'hpGlobal', 'Global Memory '.$index, null, null, null);
        }

        unset($deny, $fstype, $descr, $size, $used, $units);
    }

    unset($array);
}
