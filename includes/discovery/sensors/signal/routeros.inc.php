<?php

echo 'MIKROTIK-MIB ';

$divisor = 1;
$type    = 'routeros';

$oids = snmp_walk($device, 'mtxrWlRtabStrength', '-OsqnU', 'MIKROTIK-MIB');

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid,$value) = explode(' ', $data, 2);
        $split_oid        = explode('.', $oid);
        $index            = $split_oid[(count($split_oid) - 1)];
        $descr            = trim(snmp_get($device, '.1.3.6.1.4.1.14988.1.1.14.1.1.2.'.$index, '-Oqv'), '"') . ' Signal';

        discover_sensor($valid['sensor'], 'signal', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $value);
    }
}
