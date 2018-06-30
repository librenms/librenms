<?php

// ALCATEL-IND1-SYSTEM-MIB::systemHardwareMemoryMfg.0 = INTEGER: notreadable(12)
// ALCATEL-IND1-SYSTEM-MIB::systemHardwareMemorySize.0 = Gauge32: 268435456
// ALCATEL-IND1-HEALTH-MIB::healthDeviceMemoryLatest.0 = INTEGER: 74
// ALCATEL-IND1-HEALTH-MIB::healthDeviceMemory1MinAvg.0 = INTEGER: 74
// ALCATEL-IND1-HEALTH-MIB::healthDeviceMemory1HrAvg.0 = INTEGER: 74
// ALCATEL-IND1-HEALTH-MIB::healthDeviceMemory1HrMax.0 = INTEGER: 74
$mempool['units'] = '1';

$mempool['total'] = snmp_get($device, 'systemHardwareMemorySize.0', '-OvQ', 'ALCATEL-IND1-SYSTEM-MIB', 'aos');
$percent          = snmp_get($device, 'healthDeviceMemoryLatest.0', '-OvQ', 'ALCATEL-IND1-HEALTH-MIB', 'aos');

$mempool['used'] = ($mempool['total'] * ($percent / 100));
$mempool['free'] = ($mempool['total'] - $mempool['used']);
