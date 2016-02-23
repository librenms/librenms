<?php

// RFC1628 UPS
if (isset($config['modules_compat']['rfc1628'][$device['os']]) && $config['modules_compat']['rfc1628'][$device['os']]) {
    echo 'RFC1628 ';

    $oids = snmp_walk($device, '1.3.6.1.2.1.33.1.2.2', '-Osqn', 'UPS-MIB');
    d_echo($oids."\n");

    $oids = trim($oids);
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $current_id       = $split_oid[(count($split_oid) - 1)];
            $current_oid      = "1.3.6.1.2.1.33.1.2.2.$current_id";
            $current          = snmp_get($device, $current_oid, '-O vq');
            $descr            = 'Seconds on Battery'.(count(explode("\n", $oids)) == 1 ? '' : ' '.($current_id + 1));
            $type             = 'rfc1628';
            $index            = (500 + $current_id);

            discover_sensor($valid['sensor'], 'state', $device, $current_oid, $index, $type, $descr, '1', '1', null, null, null, null, $current);
        }
    }

    $oids = snmp_walk($device, '1.3.6.1.2.1.33.1.2.3', '-Osqn', 'UPS-MIB');
    d_echo($oids."\n");

    $oids = trim($oids);
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $current_id       = $split_oid[(count($split_oid) - 1)];
            $current_oid      = "1.3.6.1.2.1.33.1.2.3.$current_id";
            $current          = snmp_get($device, $current_oid, '-O vq');
            $descr            = 'Battery: Estimated minutes remaining'.(count(explode("\n", $oids)) == 1 ? '' : ' '.($current_id + 1));
            $type             = 'rfc1628';
            $index            = (510 + $current_id);

            discover_sensor($valid['sensor'], 'state', $device, $current_oid, $index, $type, $descr, '1', '1', null, null, null, null, $current);
        }
    }
}//end if
