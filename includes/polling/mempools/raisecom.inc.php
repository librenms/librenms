<?php

echo 'Raisecom Memory: ';

$avail  = snmp_get($device, 'raisecomMemoryAvailable.0', ['-OUvq', '-Pu'], 'RAISECOM-SYSTEM-MIB');
$total  = snmp_get($device, 'raisecomMemoryTotal.0', ['-OUvq', '-Pu'], 'RAISECOM-SYSTEM-MIB');

if (is_numeric($total) && is_numeric($avail)) {
    $mempool['total'] = $total;
    $mempool['used'] = ($total - $avail);
    $mempool['free'] = $avail;
}
