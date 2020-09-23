<?php

// Fortisandbox mempools

if ($device['os'] == 'fortisandbox') {
    echo 'FortiSandbox MemPool';
    $mempool['perc'] = snmp_get($device, 'FORTINET-FORTISANDBOX-MIB::fsaSysMemUsage.0', '-OvQ');
    $mempool['total'] = (snmp_get($device, 'FORTINET-FORTISANDBOX-MIB::fsaSysMemCapacity.0', '-OvQ')) * $mempool['mempool_precision'];
    $mempool['used'] = round($mempool['total'] * ($mempool['perc'] / 100));
    $mempool['free'] = ($mempool['total'] - $mempool['used']);
    echo '(U: ' . $mempool['used'] . ' T: ' . $mempool['total'] . ' F: ' . $mempool['free'] . ') ';
}
