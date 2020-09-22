<?php

echo 'FortiMail MemPool';
$mempool['perc'] = snmp_get($device, 'FORTINET-FORTIMAIL-MIB::fmlSysMemUsage.0', '-OvQ');
// There is no OID for MemCapacity. Using fixed value based on "diag hardware sysinfo"
$mempool['total'] = 4123984 * $mempool['mempool_precision'];
$mempool['used'] = round($mempool['total'] * ($mempool['perc'] / 100));
$mempool['free'] = ($mempool['total'] - $mempool['used']);
echo '(U: ' . $mempool['used'] . ' T: ' . $mempool['total'] . ' F: ' . $mempool['free'] . ') ';
