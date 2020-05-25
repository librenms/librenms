<?php

// FortiOS Mempool
d_echo('FortiOS Memory:');
$temp_data = snmp_get_multi_oid($device, ['fmSysMemCapacity.0', 'fmSysMemUsed.0'], '-OUQs', 'FORTINET-FORTIMANAGER-FORTIANALYZER-MIB');
$mempool['total'] = ($temp_data['fmSysMemCapacity.0'] * 1024);
$mempool['used'] = ($temp_data['fmSysMemUsed.0'] * 1024);
$mempool['perc'] = (($mempool['used'] / $mempool['total']) * 100);
$mempool['free']  = ($mempool['total'] - $mempool['used']);
unset($temp_data);
