<?php

$oid = $mempool['mempool_index'];

$oids = array(
    "cempMemPoolUsed.$oid",
    "cempMemPoolFree.$oid",
    "cempMemPoolLargestFree.$oid",
);
$data = snmp_get_multi_oid($device, $oids, '-OUQ', 'CISCO-ENHANCED-MEMPOOL-MIB');

list($mempool['used'], $mempool['free'], $mempool['largestfree']) = array_values($data);
$mempool['total'] = ($mempool['used'] + $mempool['free']);
