<?php

if ($device['os'] === 'ruckuswireless') {
    echo 'Ruckus Zone Director: ';

    $usage = snmp_get($device, '.1.3.6.1.4.1.25053.1.2.1.1.1.5.59.0', '-OvQ');

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, '0', 'ruckuswireless', 'System Memory', '100', null, null);
    }
}
