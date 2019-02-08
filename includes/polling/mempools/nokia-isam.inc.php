<?php

echo 'Nokia ISAM Memory: ';

$oid = $mempool['mempool_index'];

$oids = array(
    "memAbsoluteUsage.$oid",
    "totalMemSize.$oid",
);
$data = snmp_get_multi_oid($device, $oids, '-OUQ', 'ASAM-SYSTEM-MIB');

list($mempool['used'], $mempool['total']) = array_values($data);
$mempool['free'] = ($mempool['total'] + $mempool['used']);
