<?php

if ($device['os'] === 'ruckuswireless-unleashed') {
    echo 'Ruckus Unleashed: ';

    $usage = snmp_get($device, '.1.3.6.1.4.1.25053.1.15.1.1.1.15.14.0', '-OvQ');

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'ruckuswireless-unleashed', 'System Memory', '100', null, null);
    }
}
