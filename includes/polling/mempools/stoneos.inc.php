<?php
echo 'Pool StoneOs memory';
$mempoolGet = snmp_get_multi_oid($device, ['sysTotalMemory.0', 'sysCurMemory.0'], '-OQUs', 'HILLSTONE-SYSTEM-MIB');
$mempool['total'] = ($mempoolGet['sysTotalMemory.0'] * 1024);
$mempool['used'] = ($mempoolGet['sysCurMemory.0'] * 1024);
$mempool['free'] = ($mempool['total'] - $mempool['used']);
