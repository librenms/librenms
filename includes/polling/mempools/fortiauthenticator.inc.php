<?php

echo 'FortiAuthenticator MemPool';
$mempool['perc'] = snmp_get($device, 'FORTINET-FORTIAUTHENTICATOR-MIB::facSysMemUsage.0', '-OvQ');
// $mempool['total'] = (snmp_get($device,'FORTINET-FORTIAUTHENTICATOR-MIB::facSysMemCapacity.0', '-OvQ')) * $mempool['mempool_precision'];
// Fixed value because facSysMemCapacity is not available - MemTotal: get hardware memory
$mempool['total'] = 2056120 * $mempool['mempool_precision'];
$mempool['used'] = round($mempool['total'] * ($mempool['perc'] / 100));
$mempool['free'] = ($mempool['total'] - $mempool['used']);
echo '(U: ' . $mempool['used'] . ' T: ' . $mempool['total'] . ' F: ' . $mempool['free'] . ') ';
