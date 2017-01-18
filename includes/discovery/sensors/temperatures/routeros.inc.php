<?php

if ($device['os'] == 'routeros') {
    echo 'MIKROTIK-MIB ';

    $divisor = 10.0;
    $type    = 'mikrotik';
    $insert_index = 0;

    $oids = snmp_walk($device, 'mtxrHlTemperature', '-OsqnU', 'MIKROTIK-MIB');

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $index            = $split_oid[(count($split_oid) - 1)];
            $descr            = 'Temperature '.$index;
            $oid              = '.1.3.6.1.4.1.14988.1.1.3.10.'.$index;
            $temperature      = (snmp_get($device, $oid, '-Oqv') / $divisor);

            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $insert_index, $type, $descr, $divisor, '1', null, null, null, null, $temperature);
            $insert_index++;
        }
    }

    $oids = snmp_walk($device, 'mtxrHlProcessorTemperature', '-OsqnU', 'MIKROTIK-MIB');

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $index            = $split_oid[(count($split_oid) - 1)];
            $descr            = 'Processor Temperature '.$index;
            $oid              = '.1.3.6.1.4.1.14988.1.1.3.11.'.$index;
            $temperature      = (snmp_get($device, $oid, '-Oqv') / $divisor);

            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $insert_index, $type, $descr, $divisor, '1', null, null, null, null, $temperature);
            $insert_index++;
        }
    }
}
