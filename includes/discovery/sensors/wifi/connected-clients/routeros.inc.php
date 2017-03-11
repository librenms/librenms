<?php

if ($device['os'] == 'routeros') {
    echo 'MIKROTIK-MIB ';

    $divisor = 1;
    $type    = 'wifi';

    $oids = snmp_walk($device, 'mtxrWlApClientCount', '-OsqnU', 'MIKROTIK-MIB');
    $radio_index = 1;

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$val) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $index            = $split_oid[(count($split_oid) - 1)];
            $descr = "Radio $radio_index";

            discover_sensor($valid['sensor'], 'connected-clients', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $val);
            $radio_index++;
        }
    }
}
