<?php

// Simple hard-coded poller for Fortinet Fortigate
// Yes, it really can be this simple.
echo 'Fortimail MemPool';

// $mempool['total']	= '100';
// $usage 			= snmp_get($device, 'FORTINET-FORTIMAIL-MIB::fmlSysMemUsage.0', '-OvQ');
// $usage 			= str_replace('%', '', $usage);
// $usage                  = str_replace('"', '', $usage);
// $mempool['used']        = $usage;
// $mempool['free']        = ($mempool['total'] - $mempool['used']);

// echo '(U: '.$mempool['used'].' T: '.$mempool['total'].' F: '.$mempool['free'].') ';


$mempool['total'] = '100';
$mempool['perc'] = snmp_get($device, 'FORTINET-FORTIMAIL-MIB::fmlSysMemUsage.0', '-OvQ');
$mempool['perc'] = str_replace('%', '', $mempool['perc']);
$mempool['perc'] = str_replace('"', '', $mempool['perc']);
$mempool['used'] = $mempool['perc'];
$mempool['free'] = ($mempool['total'] - $mempool['used']);

echo '(U: '.$mempool['used'].' T: '.$mempool['total'].' F: '.$mempool['free'].') ';
