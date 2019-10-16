<?php

$temp_data = snmp_get_multi_oid($device, ['sysMemorySize.1', 'sysMemoryBusy.1'], '-OUQs', 'NAG-MIB');
$mempool['total'] = $temp_data['sysMemorySize.1'];
$mempool['used'] = $temp_data['sysMemoryBusy.1'];
$mempool['free'] = $mempool['total'] - $mempool['used'];
unset($temp_data);
