<?php

if ($device['os'] === 'ciscowlc') {
    $descr = 'Processor';
    $proc_usage = snmp_get($device, 'agentCurrentCPUUtilization.0', '-Ovq', 'AIRESPACE-SWITCHING-MIB');
    if (is_numeric($proc_usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.14179.1.1.5.1.0', '0', 'ciscowlc', $descr, '1', $proc_usage);
    }
}
