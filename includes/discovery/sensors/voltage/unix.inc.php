<?php

$oids = snmp_walk($device, 'lmVoltSensorsDevice', '-OsqnU', 'LM-SENSORS-MIB');
d_echo($oids . "\n");

if ($oids) {
    echo 'LM-SENSORS ';

    $divisor = 1000;
    $type = 'lmsensors';

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid,$descr] = explode(' ', $data, 2);
            $split_oid = explode('.', $oid);
            $index = $split_oid[(count($split_oid) - 1)];
            $oid = '.1.3.6.1.4.1.2021.13.16.4.1.3.' . $index;
            $current = floatval(snmp_get($device, $oid, '-Oqv', 'LM-SENSORS-MIB')) / $divisor;

            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
        }
    }
}
