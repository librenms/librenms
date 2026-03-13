<?php

$oids = snmp_walk($device, '.1.3.6.1.4.1.3854.1.2.2.1.16.1.4', '-Osqn', '');
d_echo($oids . "\n");

$oids = trim((string) $oids);
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
            $index = $split_oid[count($split_oid) - 1];
            $descr_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.1.$index";
            $oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.3.$index";
            $warnlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.7.$index";
            $limit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.8.$index";
            $warnlowlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.9.$index";
            $lowlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.17.1.10.$index";

            $descr = trim((string) SnmpQuery::get($descr_oid)->value(), '"');
            $humidity = SnmpQuery::get($oid)->value();
            $warnlimit = SnmpQuery::get($warnlimit_oid)->value();
            $limit = SnmpQuery::get($limit_oid)->value();
            $lowlimit = SnmpQuery::get($lowlimit_oid)->value();
            $warnlowlimit = SnmpQuery::get($warnlowlimit_oid)->value();

            discover_sensor(null, \LibreNMS\Enum\Sensor::Humidity, $device, $oid, $index, 'akcp', $descr, '1', '1', $lowlimit, $warnlowlimit, $limit, $warnlimit, $humidity);
        }
    }
}
