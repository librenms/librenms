<?php

echo 'FortiWeb MemPool';
$mempool['perc'] = snmp_get($device, 'FORTINET-FORTIWEB-MIB::fwSysMemUsage.0', '-OvQ');
$mempool['total'] = (snmp_get($device, 'FORTINET-FORTIWEB-MIB::fwSysMemCapacity.0', '-OvQ')) * $mempool['mempool_precision'];
$mempool['used'] = round($mempool['total'] * ($mempool['perc'] / 100));
$mempool['free'] = ($mempool['total'] - $mempool['used']);
echo '(U: ' . $mempool['used'] . ' T: ' . $mempool['total'] . ' F: ' . $mempool['free'] . ') ';
