<?php

for ($i = 1; $i <= 3; $i++) {
    $current_oid = ".1.3.6.1.4.1.4555.1.1.1.1.3.3.1.3.$i";
    $descr = "Input Phase $i";
    $current = snmp_get($device, $current_oid, '-Oqv');
    $type = 'netvision';
    $precision = 1;
    $index = $i;
    $lowlimit = 0;
    $warnlimit = null;
    $limit = null;

    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, null, null, $current);
}

for ($i = 1; $i <= 3; $i++) {
    $current_oid = ".1.3.6.1.4.1.4555.1.1.1.1.4.4.1.3.$i";
    $descr = "Output Phase $i";
    $current = snmp_get($device, $current_oid, '-Oqv');
    $type = 'netvision';
    $precision = 1;
    $index = (100 + $i);
    $lowlimit = 0;
    $warnlimit = null;
    $limit = null;

    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, null, null, $current);
}
