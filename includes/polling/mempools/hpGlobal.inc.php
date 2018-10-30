<?php

// NETSWITCH-MIB::hpGlobalMemSlotIndex.1 = INTEGER: 1
// NETSWITCH-MIB::hpGlobalMemSlabCnt.1 = Counter32: 3966
// NETSWITCH-MIB::hpGlobalMemFreeSegCnt.1 = Counter32: 166
// NETSWITCH-MIB::hpGlobalMemAllocSegCnt.1 = Counter32: 3803
// NETSWITCH-MIB::hpGlobalMemTotalBytes.1 = INTEGER: 11337704
// NETSWITCH-MIB::hpGlobalMemFreeBytes.1 = INTEGER: 9669104
// NETSWITCH-MIB::hpGlobalMemAllocBytes.1 = INTEGER: 1668728
if (!is_array($mempool_cache['hpGlobal'])) {
    $mempool_cache['hpGlobal'] = snmpwalk_cache_oid($device, 'hpGlobal', null, 'NETSWITCH-MIB', 'hp');
    d_echo($mempool_cache);
} else {
    d_echo('Cached!');
}

$entry = $mempool_cache['hpGlobal'][$mempool['mempool_index']];

$mempool['units'] = '1';
$mempool['used']  = $entry['hpGlobalMemAllocBytes'];
$mempool['total'] = $entry['hpGlobalMemTotalBytes'];
$mempool['free']  = $entry['hpGlobalMemFreeBytes'];
