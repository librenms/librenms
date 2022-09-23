<?php

$oids = snmp_walk($device, '.1.3.6.1.4.1.18928.1.2.2.1.9.1.2', '-OsqnU', '');
d_echo($oids . "\n");

if ($oids) {
    echo 'Areca ';
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$descr] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $oid = '.1.3.6.1.4.1.18928.1.2.2.1.9.1.3.' . $index;
        $current = snmp_get($device, $oid, '-Oqv', '');

        discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'areca', trim($descr, '"'), '1', '1', null, null, null, null, $current);
    }
}
