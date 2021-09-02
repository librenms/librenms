<?php

$oids = snmp_walk($device, '.1.3.6.1.4.1.18928.1.2.2.1.8.1.2', '-OsqnU', '');
d_echo($oids . "\n");

if ($oids) {
    echo 'Areca ';

    $divisor = 1000;
    $type = 'areca';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid,$descr] = explode(' ', $data, 2);
            $split_oid = explode('.', $oid);
            $index = $split_oid[(count($split_oid) - 1)];
            $oid = '.1.3.6.1.4.1.18928.1.2.2.1.8.1.3.' . $index;
            $current = (snmp_get($device, $oid, '-Oqv', '') / $divisor);
            if (trim($descr, '"') != 'Battery Status') {
                // Battery Status is charge percentage, or 255 when no BBU
                discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, trim($descr, '"'), $divisor, '1', null, null, null, null, $current);
            }
        }
    }
}
