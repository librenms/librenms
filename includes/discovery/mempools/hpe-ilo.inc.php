<?php

if ($device['os'] == 'hpe-ilo') {
    echo 'HP ILO4 ';

    $mem_free = snmp_get($device, 'cpqHoPhysicalMemoryFree.0', '-OvQ', 'CPQHOST-MIB');
    $mem_capacity = snmp_get($device, 'cpqHoPhysicalMemorySize.0', '-OvQ', 'CPQHOST-MIB');

    if ((is_numeric($mem_free)) && (is_numeric($mem_capacity))) {
        discover_mempool($valid_mempool, $device, 0, 'hpe-ilo', 'Physical Memory', '1', null, null);
    }

    $page_free = snmp_get($device, 'cpqHoPagingMemoryFree.0', '-OvQ', 'CPQHOST-MIB');
    $page_capacity = snmp_get($device, 'cpqHoPagingMemorySize.0', '-OvQ', 'CPQHOST-MIB');

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
