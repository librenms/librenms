<?php
if ($device['os'] == 'dnos') {
    echo 'DNOS CPU: ';
    
    $descr = 'CPU';
    preg_match('/(\d*\.\d*)/', snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '-OvQ'), $matches);
    $usage = $matches[0];

    discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '0', 'dnos-cpu', $descr, '1', $usage, null, null);
}
