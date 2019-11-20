<?php

if ($device['os'] == 'hpe-ilo') {
    echo 'HPE ILO4 ';

    $memory_pool = snmp_get_multi_oid($device, ['cpqHoPhysicalMemoryFree.0', 'cpqHoPhysicalMemorySize.0', 'cpqHoPagingMemoryFree.0', 'cpqHoPagingMemorySize.0'], '-OQUs', 'CPQHOST-MIB');

    $mem_free = $memory_pool['cpqHoPhysicalMemoryFree.0'];
    $mem_capacity = $memory_pool['cpqHoPhysicalMemorySize.0'];
    $page_free = $memory_pool['cpqHoPagingMemoryFree.0'];
    $page_capacity = $memory_pool['cpqHoPagingMemorySize.0'];


    if ((is_numeric($mem_free)) && (is_numeric($mem_capacity))) {
        discover_mempool($valid_mempool, $device, 0, 'hpe-ilo', 'Physical Memory', '1', null, null);
    }
    if ((is_numeric($page_free)) && (is_numeric($page_capacity))) {
        discover_mempool($valid_mempool, $device, 1, 'hpe-ilo', 'Paging Memory', '1', null, null);
    }
}

unset(
    $mem_free,
    $mem_capacity,
    $page_free,
    $page_capacity
);
