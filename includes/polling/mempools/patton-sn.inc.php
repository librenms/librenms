<?php

$oid = $mempool['mempool_index'];

d_echo('Patton SN Mempool');

if (!is_array($mempool_cache['patton-sn'])) {
    d_echo('caching');

    $mempool_cache['patton-sn'] = [];
    $mempool_cache['patton-sn'] = snmpwalk_cache_multi_oid($device, 'memAllocatedBytes', $mempool_cache['patton-sn'], 'SMARTNODE-MIB', 'patton');
    $mempool_cache['patton-sn'] = snmpwalk_cache_multi_oid($device, 'memFreeBytes', $mempool_cache['patton-sn'], 'SMARTNODE-MIB', 'patton');
    d_echo($mempool_cache);
}

$entry = $mempool_cache['patton-sn'][$mempool['mempool_index']];

if ($entry['hwEntityMemSize'] < 0) {
    $entry['hwEntityMemSize'] = ($entry['hwEntityMemSize'] * -1);
}

$mempool['total'] = $entry['memAllocatedBytes'] + $entry['memFreeBytes'] ;
$mempool['used']  = $entry['memAllocatedBytes'];
$mempool['free']  = $entry['memFreeBytes'];
$perc             = $entry['used']/$entry['total'] * 100;

d_echo($mempool);
