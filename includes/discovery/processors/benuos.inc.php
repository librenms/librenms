<?php

if ($device['os'] == 'benu') {
    echo 'BENU CPU: ';

    $descr = 'Processor';
    $usage = snmp_get($device, 'bSysAvgCPUUtil5Min.0', '-OvQs', 'BENU-HOST-MIB');
    discover_processor($valid['processor'], $device, $oid, 1, 'benu', 'Processor', '1', $usage);
}
