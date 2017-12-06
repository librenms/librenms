<?php
if ($device['os'] == 'datacom') {
    echo 'Datacom Switch : ';
    $descr = 'Processor';
    $usage = snmp_get($device, 'swCpuUsage.0', '-Ovq', 'DMswitch-MIB');
    echo $usage."\n";
    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.3709.3.5.201.1.1.10.0', '0', 'datacom', $descr, '1', substr($usage, 0, 2), null, null);
    }
}
