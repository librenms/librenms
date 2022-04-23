<?php

$oids = snmp_walk($device, '.1.3.6.1.4.1.3854.1.2.2.1.16.1.4', '-Osqn', '');
d_echo($oids . "\n");

$oids = trim($oids);
if ($oids) {
    echo 'AKCP ';
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$status] = explode(' ', $data, 2);
        if ($status == 2) {
            // 2 = normal, 0 = not connected
            $split_oid = explode('.', $oid);
            $index = $split_oid[(count($split_oid) - 1)];
            $descr_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.1.$index";
            $oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.3.$index";
            $warnlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.7.$index";
            $limit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.8.$index";
            $warnlowlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.9.$index";
            $lowlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.10.$index";

            $descr = trim(snmp_get($device, $descr_oid, '-Oqv', ''), '"');
            $humidity = snmp_get($device, $oid, '-Oqv', '');
            $warnlimit = snmp_get($device, $warnlimit_oid, '-Oqv', '');
            $limit = snmp_get($device, $limit_oid, '-Oqv', '');
            $lowlimit = snmp_get($device, $lowlimit_oid, '-Oqv', '');
            $warnlowlimit = snmp_get($device, $warnlowlimit_oid, '-Oqv', '');

            discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, 'akcp', $descr, '1', '1', $lowlimit, $warnlowlimit, $limit, $warnlimit, $humidity);
        }
    }
}
