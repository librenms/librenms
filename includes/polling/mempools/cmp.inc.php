<?php

$oid = $mempool['mempool_index'];

$oids = [
    "ciscoMemoryPoolUsed.$oid",
    "ciscoMemoryPoolFree.$oid",
    "ciscoMemoryPoolLargestFree.$oid",
];
$data = snmp_get_multi_oid($device, $oids, '-OUQ', 'CISCO-MEMORY-POOL-MIB');

[$mempool['used'], $mempool['free'], $mempool['largestfree']] = array_values($data);
$mempool['total'] = ($mempool['used'] + $mempool['free']);
