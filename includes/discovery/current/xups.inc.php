<?php

// XUPS-MIB
if ($device['os'] == 'powerware') {
    echo 'XUPS-MIB ';

    $oids = snmp_walk($device, 'xupsBatCurrent', '-Osqn', 'XUPS-MIB');
    d_echo($oids."\n");

    $oids = trim($oids);
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $current_id       = $split_oid[(count($split_oid) - 1)];
            $current_oid      = "1.3.6.1.4.1.534.1.2.3.$current_id";
            $divisor          = 1;
            $current          = snmp_get($device, $current_oid, '-O vq');
            $descr            = 'Battery'.(count(explode("\n", $oids)) == 1 ? '' : ' '.($current_id + 1));
            $type             = 'xups';
            $index            = '1.2.3.'.$current_id;

            discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }

    $oids = trim(snmp_walk($device, 'xupsOutputCurrent', '-OsqnU', 'XUPS-MIB'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        $current_oid = "1.3.6.1.4.1.534.1.4.4.1.3.$i";
        $descr   = 'Output';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $current = snmp_get($device, $current_oid, '-Oqv');
        $type    = 'xups';
        $divisor = 1;
        $index   = '4.4.1.3.'.$i;

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    $oids = trim(snmp_walk($device, 'xupsInputCurrent', '-OsqnU', 'XUPS-MIB'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        $current_oid = "1.3.6.1.4.1.534.1.3.4.1.3.$i";
        $descr       = 'Input';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $current = snmp_get($device, $current_oid, '-Oqv');
        $type    = 'xups';
        $divisor = 1;
        $index   = '3.4.1.3.'.$i;

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}//end if
