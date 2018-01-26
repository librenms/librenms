<?php
if ($device['os'] === 'stoneos') {
    $cpuUsage = snmp_get($device, 'HILLSTONE-SYSTEM-MIB::sysCPU.0', '-OvQU');
    if (is_numeric($cpuUsage)) {
        discover_processor($valid['processor'], $device, 'HILLSTONE-SYSTEM-MIB::sysCPU.0', '0', 'stoneos', 'Processor', '1', $usage, null, null);
    }
}
