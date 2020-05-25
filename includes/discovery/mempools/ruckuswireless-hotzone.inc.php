<?php

if ($device['os'] === 'ruckuswireless-hotzone') {
    echo 'Ruckus Hotzone: ';

    $usage = snmp_get($device, '.1.3.6.1.4.1.25053.1.1.11.1.1.1.2.0', '-OvQ');

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'ruckuswireless-hotzone', 'System Memory', '100', null, null);
    }
}
