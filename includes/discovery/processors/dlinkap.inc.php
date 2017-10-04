<?php

if ($device['os'] === 'dlinkap') {
    echo 'Dlink AP : ';

    $processor_oid=$device['sysObjectID'].'.5.1.3.0';
    $descr = 'Processor';
    $usage = snmp_get($device, $processor_oid, '-OvQ');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, $processor_oid, '0', 'dlinkap-cpu', $descr, '100', $usage, null, null);
    }
}
