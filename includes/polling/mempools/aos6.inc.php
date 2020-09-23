<?php

$mempool['units'] = '1';

$mempool['total'] = snmp_get($device, 'systemHardwareMemorySize.0', '-OvQ', 'ALCATEL-IND1-SYSTEM-MIB');
$percent = snmp_get($device, 'healthDeviceMemoryLatest.0', '-OvQ', 'ALCATEL-IND1-HEALTH-MIB');

$mempool['used'] = ($mempool['total'] * ($percent / 100));
$mempool['free'] = ($mempool['total'] - $mempool['used']);
