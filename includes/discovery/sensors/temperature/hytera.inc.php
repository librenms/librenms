<?php

$oids = snmp_walk($device, 'rptPaTemprature', '-OsqnU', 'HYTERA-REPEATER-MIB');
d_echo($oids);
if ($oids !== false) {
    echo 'HYTERA-REPEATER-MIB ';
}
$divisor = 1;
$type = 'hytera';

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$descr] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $index = $split_oid[count($split_oid) - 1];
        $descr = 'PA Temperature ' . $index;
        $oid = '.1.3.6.1.4.1.40297.1.2.1.2.2.' . $index;
        $temperature = hytera_h2f(str_replace('"', '', snmp_get($device, $oid, '-Oqv')), 2);

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', 0, 0, 70, 75, $temperature);
    }
}
