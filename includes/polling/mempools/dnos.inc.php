<?php

// Code below was borrowed from 'powerconnect-cpu.inc.php' 

if (preg_match('/.6027.1.3.[0-9]+$/', $device['sysObjectID'])) {
    $mempool['total'] = snmp_get($device, 'chSysProcessorMemSize.1', '-OvQU', 'F10-S-SERIES-CHASSIS-MIB');
    $mempool['used']  = $mempool['total'] * (snmp_get($device, 'chStackUnitMemUsageUtil.1', '-OvQU', 'F10-S-SERIES-CHASSIS-MIB')/ 100);
    $mempool['free']  = ($mempool['total'] - $mempool['used']);
} elseif (preg_match('/.6027.1.2.[0-9]+$/', $device['sysObjectID'])) {
    $mempool['total'] = snmp_get($device, 'chSysProcessorMemSize.1', '-OvQU', 'F10-C-SERIES-CHASSIS-MIB');
    $mempool['used']  = $mempool['total'] * (snmp_get($device, 'chStackUnitMemUsageUtil.1', '-OvQU', 'F10-C-SERIES-CHASSIS-MIB')/ 100);
    $mempool['free']  = ($mempool['total'] - $mempool['used']);
} elseif (preg_match('/.6027.1.4.[0-9]+$/', $device['sysObjectID'])) {
    $mempool['total'] = snmp_get($device, 'dellNetProcessorMemSize.stack.1.1', '-OvQU', 'DELL-NETWORKING-CHASSIS-MIB');
    $mempool['used']  = $mempool['total'] * (snmp_get($device, 'dellNetCpuUtilMemUsage.stack.1.1', '-OvQU', 'DELL-NETWORKING-CHASSIS-MIB')/ 100);
    $mempool['free']  = ($mempool['total'] - $mempool['used']);
} else {
    $mempool['total'] = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.2.0', '-OvQ');
    $mempool['free']  = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.1.0', '-OvQ');
    $mempool['used']  = ($mempool['total'] - $mempool['free']);
}
