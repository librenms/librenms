<?php

if ($device['os'] === 'secureplatform') {
    echo 'SecurePlatform : ';

    $descr = 'Processor';
    $usage = snmp_get($device, '.1.3.6.1.4.1.2620.1.6.7.2.4.0', '-OvQ', 'CHECKPOINT-MIB');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.2620.1.6.7.2.4.0', '0', 'splat-cpu', $descr, '1', $usage, null, null);
    }
}
