<?php
if ($device['os'] === 'stoneos') {
    $cpuUsage = snmp_get($device, 'sysCPU.0', '-OvQU', 'HILLSTONE-SYSTEM-MIB');
    if (is_numeric($cpuUsage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.28557.2.2.1.3.0', '0', 'stoneos', 'Processor', '1', $usage, null, null);
    }
}
