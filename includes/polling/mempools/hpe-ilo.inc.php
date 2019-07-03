<?php

$data = snmp_get_multi_oid($device, ['cpqHoPhysicalMemorySize.0', 'cpqHoPhysicalMemoryFree.0', 'cpqHoPagingMemorySize.0', 'cpqHoPagingMemoryFree.0'], '-OUQs', 'CPQHOST-MIB');
$units = 1024*1024;

if ($mempool['mempool_index'] == 0) {
    $mem_capacity = $data['cpqHoPhysicalMemorySize.0'];
    $mem_free = $data['cpqHoPhysicalMemoryFree.0'];

    $mempool['total'] = $mem_capacity*$units;
    $mempool['free']  = $mem_free*$units;
    $mempool['used']  = $mempool['total'] - $mempool['free'];
}

if ($mempool['mempool_index'] == 1) {
    $page_capacity = $data['cpqHoPagingMemorySize.0'];
    $page_free = $data['cpqHoPagingMemoryFree.0'];

    $mempool['total'] = $page_capacity*$units;
    $mempool['free']  = $page_free*$units;
    $mempool['used']  = $mempool['total'] - $mempool['free'];
}

unset(
    $mem_free,
    $mem_capacity,
    $page_free,
    $page_capacity
);
