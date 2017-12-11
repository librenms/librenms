<?php

if ($device['os'] === 'benuos') {
    echo 'BENU CPU: ';

    $descr = 'Processor';
    $oid = '.1.3.6.1.4.1.39406.1.5.1.8.0';
    $usage = snmp_get($device, 'bSysAvgCPUUtil5Min.0', '-OvQs', 'BENU-HOST-MIB');
    discover_processor($valid['processor'], $device, $oid, 1, 'benuos', 'Processor', '1', $usage);
}
