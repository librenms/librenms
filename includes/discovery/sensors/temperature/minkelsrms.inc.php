<?php

$oids = snmp_walk($device, '.1.3.6.1.4.1.3854.1.2.2.1.16.1.4', '-Osqn', '');
d_echo($oids . "\n");

$oids = trim($oids);
if ($oids) {
    echo 'Minkels RMS ';
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$status] = explode(' ', $data, 2);
        if ($status == 2) {
            // 2 = normal, 0 = not connected
            $split_oid = explode('.', $oid);
            $temperature_id = $split_oid[(count($split_oid) - 1)];
            $descr_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.1.$temperature_id";
            $temperature_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.3.$temperature_id";
            $warnlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.7.$temperature_id";
            $limit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.8.$temperature_id";
            $lowwarnlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.9.$temperature_id";
            $lowlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.10.$temperature_id";

            $descr = trim(snmp_get($device, $descr_oid, '-Oqv', ''), '"');
            $temperature = snmp_get($device, $temperature_oid, '-Oqv', '');
            $lowwarnlimit = snmp_get($device, $lowwarnlimit_oid, '-Oqv', '');
            $warnlimit = snmp_get($device, $warnlimit_oid, '-Oqv', '');
            $limit = snmp_get($device, $limit_oid, '-Oqv', '');
            $lowlimit = snmp_get($device, $lowlimit_oid, '-Oqv', '');

            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'akcp', $descr, '1', '1', $lowlimit, $low_warn_limit, $warnlimit, $limit, $temperature);
        }
    }
}
