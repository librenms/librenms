<?php

if ($device['os'] === 'sti410c') {
    $descr = 'Processor';
    $proc_usage = snmp_get($device, '.1.3.6.1.4.1.30631.1.9.1.1.3.0', '-Ovq');
    if (is_numeric($proc_usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.30631.1.9.1.1.3.0', '0', 'sti410c', $descr, '1', $proc_usage);
    }
}
