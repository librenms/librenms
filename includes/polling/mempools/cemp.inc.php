<?php

$oid = $mempool['mempool_index'];

$HCoids = [
    "cempMemPoolHCUsed.$oid",
    "cempMemPoolHCFree.$oid",
    "cempMemPoolHCLargestFree.$oid",
];
$oids = [
    "cempMemPoolUsed.$oid",
    "cempMemPoolFree.$oid",
    "cempMemPoolLargestFree.$oid",
];
$data = snmp_get_multi_oid($device, $HCoids, '-OUQ', 'CISCO-ENHANCED-MEMPOOL-MIB');
if (count($data) < 2) {
    $data = snmp_get_multi_oid($device, $oids, '-OUQ', 'CISCO-ENHANCED-MEMPOOL-MIB');
}
[$mempool['used'], $mempool['free'], $mempool['largestfree']] = array_values($data);
$mempool['total'] = ($mempool['used'] + $mempool['free']);
