<?php

//
// Polling for "fs-gbn" mempool_type, which belongs to Fiberstore devices, using
// GBNPlatformOAM-MIB mib
//
//

$oid = $mempool['mempool_index'];

d_echo('FS Mempool');

if (!is_array($mempool_cache['fs'])) {
    d_echo('caching'); //only occurs the first time if multiple mempools are polled
    $mempool_cache['fs'] = array();
    $mempool_cache['fs'] = snmpwalk_cache_oid($device, 'memorySize', $mempool_cache['fs'], 'GBNPlatformOAM-MIB', 'fs');
    $mempool_cache['fs'] = snmpwalk_cache_oid($device, 'memoryIdle', $mempool_cache['fs'], 'GBNPlatformOAM-MIB', 'fs');
    d_echo($mempool_cache);
}

$entry = $mempool_cache['fs'][$oid];
//
// The mib states that it should be in bytes already, but in real life, it is not so true
// We suppose that any value below 32768 is clearly not bytes and we understand it as MBytes
//
if ($entry['memorySize'] < (32 * 1024)) {
    $entry['memorySize'] = ($entry['memorySize'] * 1024 * 1024);
    $entry['memoryIdle'] = ($entry['memoryIdle'] * 1024 * 1024);
}
//
// little math for usage in percents, and absolute values.
//
if ($entry['memorySize'] > 0) {
    $perc             = ($entry['memorySize'] - $entry['memoryIdle']) / $entry['memorySize'] * 100;
    $mempool['total'] = $entry['memorySize'];
    $mempool['used']  = ($entry['memorySize'] - $entry['memoryIdle']);
    $mempool['free']  = ($entry['memoryIdle']);
}
