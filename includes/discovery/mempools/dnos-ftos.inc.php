<?php

// Code borrowed and modified from 'powerconnect-cpu.inc.php' 

if ($device['os'] == 'dnos' || $device['os'] == 'ftos') {
    echo 'DNOS-MEMORY-POOL:  ';

    if (preg_match('/.6027.1.3.[0-9]+$/', $device['sysObjectID'])) {
        $total = snmp_get($device, 'chSysProcessorMemSize.1', '-OvQU', 'F10-S-SERIES-CHASSIS-MIB');
        if (is_numeric($total)) {
            discover_mempool($valid_mempool, $device, 0, $device['os'], 'Memory Utilization', '1', null, null);
        }
    } elseif (preg_match('/.6027.1.2.[0-9]+$/', $device['sysObjectID'])) {
        $total = snmp_get($device, 'chSysProcessorMemSize.1', '-OvQU', 'F10-C-SERIES-CHASSIS-MIB');
        if (is_numeric($total)) {
            discover_mempool($valid_mempool, $device, 0, $device['os'], 'Memory Utilization', '1', null, null);
        }
    } elseif (preg_match('/.6027.1.4.[0-9]+$/', $device['sysObjectID'])) {
        $total = str_replace(' percent', '', snmp_get($device, 'dellNetCpuUtilMemUsage.stack.1.1', '-OvQ', 'DELL-NETWORKING-CHASSIS-MIB'));
        if (is_numeric($total)) {
            discover_mempool($valid_mempool, $device, 0, $device['os'], 'Memory Utilization', '1', null, null);
        }
    } else {
        $free = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.1.0', '-OvQ');
        if (is_numeric($free)) {
            discover_mempool($valid_mempool, $device, 0, $device['os'], 'Memory Utilization', '1', null, null);
        }
    }
}
