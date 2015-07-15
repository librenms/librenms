<?php

// Simple hard-coded poller for Fortinet Fortigate
// Yes, it really can be this simple.
echo 'Fortigate MemPool';

$mempool['perc']  = snmp_get($device, 'FORTINET-FORTIGATE-MIB::fgSysMemUsage.0', '-OvQ');
$mempool['total'] = snmp_get($device, 'FORTINET-FORTIGATE-MIB::fgSysMemCapacity.0', '-OvQ');
$mempool['used']  = ($mempool['total'] * ($mempool['perc'] / 100));
$mempool['free']  = ($mempool['total'] - $mempool['used']);

echo '(U: '.$mempool['used'].' T: '.$mempool['total'].' F: '.$mempool['free'].') ';
