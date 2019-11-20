<?php

$oid = $mempool['mempool_index'];

d_echo('Huawei VRP Mempool');

if (!is_array($mempool_cache['vrp'])) {
    d_echo('caching');

    $mempool_cache['vrp'] = array();
    $mempool_cache['vrp'] = snmpwalk_cache_multi_oid($device, 'hwEntityMemSize', $mempool_cache['vrp'], 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
    $mempool_cache['vrp'] = snmpwalk_cache_multi_oid($device, 'hwEntityMemSizeMega', $mempool_cache['vrp'], 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
    $mempool_cache['vrp'] = snmpwalk_cache_multi_oid($device, 'hwEntityMemUsage', $mempool_cache['vrp'], 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
    d_echo($mempool_cache);
}

$entry = $mempool_cache['vrp'][$mempool['mempool_index']];

if ($entry['hwEntityMemSize'] < 0) {
    $entry['hwEntityMemSize'] = ($entry['hwEntityMemSize'] * -1);
}

$perc             = $entry['hwEntityMemUsage'];
$mempool['total'] = max($entry['hwEntityMemSize'], $entry['hwEntityMemSizeMega'] * 1024 * 1024);
$mempool['used']  = ($mempool['total'] / 100 * $perc);
$mempool['free']  = ($mempool['total']  - $mempool['used']);
