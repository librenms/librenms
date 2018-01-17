<?php

if ($device['os'] === 'benuos') {
    echo 'BENU CPU: ';

    $descr = 'Processor';
    $usage = snmp_get($device, 'bSysAvgCPUUtil5Min.0', '-OvQs', 'BENU-HOST-MIB');
    discover_processor($valid['processor'], $device, $oid, 1, 'benuos', 'Processor', '1', $usage);
}
