<?php
//
// Polling for "fs-switch" mempool_type, which belongs to Fiberstore devices, using
// SWITCH mib
//
$oid = $mempool['mempool_index'];

d_echo('FS Mempool (SWITCHMIB)');

if (!is_array($mempool_cache['fs-switchmib'])) {
    d_echo('caching');
    $mempool_cache['fs-switchmib'] = snmpwalk_cache_oid($device, 'memTotalReal', [], 'SWITCH', 'fs');
    $mempool_cache['fs-switchmib'] = snmpwalk_cache_oid($device, 'memTotalFree', $mempool_cache['fs-switchmib'], 'SWITCH', 'fs');
    $mempool_cache['fs-switchmib'] = snmpwalk_cache_oid($device, 'memTotalUsed', $mempool_cache['fs-switchmib'], 'SWITCH', 'fs');
    d_echo($mempool_cache);
}

$entry = $mempool_cache['fs-switchmib'][$oid];
//
// Let's do some simple calculation
//
if ($entry['memTotalReal'] > 0) {
    $perc             = ($entry['memTotalUsed']) / $entry['memTotalReal'] * 100;
    $mempool['total'] = ($entry['memTotalReal'] * 1024);
    $mempool['used']  = ($entry['memTotalUsed'] * 1024);
    $mempool['free']  = ($entry['memTotalFree'] * 1024);
}
