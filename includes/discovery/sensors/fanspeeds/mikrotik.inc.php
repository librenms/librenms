<?php

if ($device['os'] == 'routeros') {
    $oids = snmp_walk($device, 'mtxrHlActiveFan', '-OsqnU', 'MIKROTIK-MIB');
    d_echo($oids."\n");

    if ($oids !== false) {
        echo 'MIKROTIK-MIB ';
    }

    $divisor = 1;
    $type    = 'mikrotik';

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $index            = $split_oid[(count($split_oid) - 1)];
            $descr            = 'Fan '.$index;
            $oid              = '.1.3.6.1.4.1.14988.1.1.3.9.'.$index;
            $fanspeed         = (snmp_get($device, $oid, '-Oqv') / $divisor);
            if ($fanspeed > 0) {
                discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $fanspeed);
            }
        }
    }
}
