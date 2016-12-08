<?php

// Code borrowed and modified from 'powerconnect-cpu.inc.php' 

if ($device['os'] == 'dnos') {
    echo 'DNOS-MEMORY-POOL:  ';

    $free = snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.1.0', '-OvQ');

    if (is_numeric($free)) {
        discover_mempool($valid_mempool, $device, 0, 'dnos-mem', 'Memory Utilization', '1', null, null);
    }
}
