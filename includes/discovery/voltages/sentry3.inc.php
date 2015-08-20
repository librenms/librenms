<?php

if ($device['os'] == 'sentry3') {
    $oids = snmp_walk($device, 'infeedVoltage', '-OsqnU', 'Sentry3-MIB');
    d_echo($oids."\n");

    if ($oids) {
        echo 'Sentry3-MIB ';
    }

    $divisor = 10;
    $type    = 'sentry3';

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $descr            = 'Tower '.$index;
            $index            = $split_oid[(count($split_oid) - 1)];
            $oid              = '1.3.6.1.4.1.1718.3.2.2.1.11.1.'.$index;
            $current          = (snmp_get($device, $oid, '-Oqv') / $divisor);

            discover_sensor($valid['sensor'], 'voltage', $device,
                $oid, $index, $type,
                $descr, $divisor, '1', null, null, null, null, $current);
        }
    }
}//end if
