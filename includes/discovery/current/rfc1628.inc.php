<?php

// RFC1628 UPS
if (isset($config['modules_compat']['rfc1628'][$device['os']]) && $config['modules_compat']['rfc1628'][$device['os']]) {
    echo 'RFC1628 ';

    $oids = snmp_walk($device, '1.3.6.1.2.1.33.1.2.6', '-Osqn', 'UPS-MIB');
    d_echo($oids."\n");

    $oids = trim($oids);
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $current_id       = $split_oid[(count($split_oid) - 1)];
            $current_oid      = "1.3.6.1.2.1.33.1.2.6.$current_id";
            $precision        = 10;
            $current          = (snmp_get($device, $current_oid, '-O vq') / $precision);
            $descr            = 'Battery'.(count(explode("\n", $oids)) == 1 ? '' : ' '.($current_id + 1));
            $type             = 'rfc1628';
            $index            = (500 + $current_id);

            discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', null, null, null, null, $current);
        }
    }

    $oids = trim(snmp_walk($device, '1.3.6.1.2.1.33.1.4.3.0', '-OsqnU'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        $current_oid = ".1.3.6.1.2.1.33.1.4.4.1.3.$i";
        $descr       = 'Output';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $precision = 10;
        $current   = (snmp_get($device, $current_oid, '-Oqv') / $precision);
        $type      = 'rfc1628';
        $index     = $i;

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', null, null, null, null, $current);
    }

    $oids = trim(snmp_walk($device, '1.3.6.1.2.1.33.1.3.2.0', '-OsqnU'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        $current_oid = "1.3.6.1.2.1.33.1.3.3.1.4.$i";
        $descr       = 'Input';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $precision = 10;
        $current   = (snmp_get($device, $current_oid, '-Oqv') / $precision);
        $type      = 'rfc1628';
        $index     = (100 + $i);

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', null, null, null, null, $current);
    }

    $oids = trim(snmp_walk($device, '1.3.6.1.2.1.33.1.5.2.0', '-OsqnU'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        $current_oid = "1.3.6.1.2.1.33.1.5.3.1.3.$i";
        $descr       = 'Bypass';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $precision = 10;
        $current   = (snmp_get($device, $current_oid, '-Oqv') / $precision);
        $type      = 'rfc1628';
        $index     = (200 + $i);

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', null, null, null, null, $current);
    }
}//end if
