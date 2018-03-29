<?php
/**
 * Memory percent
 */
$cardIndex = 'mgrCardMemUtil.'.$mempool['mempool_index'];
$usage     = snmp_get($device, $cardIndex, '-Ovq', 'GEPON-OLT-COMMON-MIB');
$perc      = round($usage / 100);
/*
 * Manual memory 256Mb on each board
 */
$memory_available = (256 * pow(1024, 2));
$mempool['total'] = $memory_available;
if (is_numeric($perc)) {
    $mempool['used'] = ($memory_available / 100 * $perc);
    $mempool['free'] = ($memory_available - $mempool['used']);
}
