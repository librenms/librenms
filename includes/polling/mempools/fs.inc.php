<?php

$oid = $mempool['mempool_index'];

d_echo('FS Mempool');

if (!is_array($mempool_cache['fs'])) {
    d_echo('caching');


    $mempool_cache['fs'] = array();
    $mempool_cache['fs'] = snmpwalk_cache_multi_oid($device, 'memorySize', $mempool_cache['fs'], 'GBNPlatformOAM-MIB', 'fs');
    $mempool_cache['fs'] = snmpwalk_cache_multi_oid($device, 'memoryIdle', $mempool_cache['fs'], 'GBNPlatformOAM-MIB', 'fs');
    d_echo($mempool_cache);
}

$entry = $mempool_cache['fs'][$mempool['mempool_index']];

if ($entry['memorySize'] < (32 * 1024)) {
    $entry['memorySize'] = ($entry['memorySize'] * 1024 * 1024);
    $entry['memoryIdle'] = ($entry['memoryIdle'] * 1024 * 1024);
}

$perc             = ($entry['memorySize'] - $entry['memoryIdle']) / $entry['memorySize'] * 100;
$mempool['total'] = $entry['memorySize'];
$mempool['used']  = ($entry['memorySize'] - $entry['memoryIdle']);
$mempool['free']  = ($entry['memoryIdle']);
