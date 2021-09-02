<?php

if ($device['os'] === 'terra') {
    $query = [
        ['sti410C', '.1.3.6.1.4.1.30631.1.9.1.1.5.0'],
        ['sti440',  '.1.3.6.1.4.1.30631.1.18.1.326.5.0'],
    ];

    foreach ($query as $row) {
        if (strpos($device['sysDescr'], $row[0]) !== false) {
            $c = snmp_get($device, $row[1], '-Oqv') / 10;
            if (is_numeric($c)) {
                discover_sensor($valid['sensor'], 'voltage', $device, $row[1], 0, $row[0], 'Supply voltage', 10, 1, null, null, null, null, $c);
            }
        }
    }

    unset($query);
}
