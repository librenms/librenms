<?php

if (! is_array($mempool_cache['ies5000-mem'])) {
    $mempool_cache['ies5000-mem'] = snmpwalk_cache_oid($device, 'memoryUsageTable', null, 'ZYXEL-IES5000-MIB', 'zyxel', ['-LE 3', '-OQUs', '-Pu']);
    d_echo($mempool_cache);
} else {
    d_echo('Cached!');
}

$entry = $mempool_cache['ies5000-mem'][$mempool['mempool_index']];
$mempool['total'] = 100;
$mempool['perc'] = $entry['memoryCurValue'];
$mempool['used'] = $mempool['perc'];
$mempool['free'] = ($mempool['total'] - $mempool['used']);

$mempool['perc_warn'] = $entry['memoryHighThresh'];
