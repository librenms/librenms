<?php

echo 'RBN-ENVMON-MIB ';

$type = 'seos';
$insert_index = 0;

$oids = snmp_walk($device, 'rbnCpuTempDescr', '-OsqnU', 'RBN-ENVMON-MIB');

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$descr] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 2)] . '.' . $split_oid[(count($split_oid) - 1)];
        $oid = '.1.3.6.1.4.1.2352.2.4.1.4.1.3.' . $index;
        $temperature = snmp_get($device, $oid, '-Oqv');
        $descr = str_replace('"', '', $descr);

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $insert_index, $type, $descr, 1, '1', null, null, null, null, $temperature);
        $insert_index++;
    }
}

$oids = snmp_walk($device, 'rbnEntityTempDescr', '-OsqnU', 'RBN-ENVMON-MIB');

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$descr] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 2)] . '.' . $split_oid[(count($split_oid) - 1)];
        $oid = '.1.3.6.1.4.1.2352.2.4.1.6.1.3.' . $index;
        $temperature = snmp_get($device, $oid, '-Oqv');
        $descr = str_replace('"', '', $descr);

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $insert_index, $type, $descr, 1, '1', null, null, null, null, $temperature);
        $insert_index++;
    }
}
