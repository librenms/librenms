<?php

$oids = snmp_walk($device, 'lmFanSensorsDevice', '-OsqnU', 'LM-SENSORS-MIB');
d_echo($oids . "\n");

$oids = trim($oids);
if ($oids) {
    echo 'LM-SENSORS ';
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$descr] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $oid = '.1.3.6.1.4.1.2021.13.16.3.1.3.' . $index;
        $current = snmp_get($device, $oid, '-Oqv', 'LM-SENSORS-MIB');
        $descr = trim(str_ireplace('fan-', '', $descr));
        if ($current !== false && $current >= 0) {
            discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'lmsensors', $descr, '1', '1', null, null, null, null, $current);
        }
    }
}
