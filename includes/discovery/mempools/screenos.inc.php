<?php

if ($device['os'] == 'screenos') {
    echo 'ScreenOS: ';

    $used  = snmp_get($device, '.1.3.6.1.4.1.3224.16.2.1.0', '-OvQ');
    $free  = snmp_get($device, '.1.3.6.1.4.1.3224.16.2.2.0', '-OvQ');
    $total = ($free + $used);

    $percent = ($used / $total * 100);

    if (is_numeric($total) && is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, 'screenos', 'Memory', '1', null, null);
    }
}
