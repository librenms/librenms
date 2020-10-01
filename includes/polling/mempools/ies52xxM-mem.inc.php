<?php

if (! is_array($mempool_cache['ies-mem'])) {
    $mempool_cache['ies-mem'] = snmpwalk_cache_oid($device, 'memStatsTable', null, 'IES5206-MIB', 'zyxel');
    d_echo($mempool_cache);
} else {
    d_echo('Cached!');
}

$entry = $mempool_cache['ies-mem'][$mempool['mempool_index']];

$mempool['total'] = 100;
$mempool['perc'] = $entry['memStatsCurrent'];
$mempool['used'] = $mempool['perc'];
$mempool['free'] = ($mempool['total'] - $mempool['used']);

$mempool['perc_warn'] = $entry['memStatsHighThreshold'];
