<?php

$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.2.3.1.0', '-OsqnU');
d_echo($oids . "\n");

// Try High-Precision First
if (! empty($oids)) {
    echo 'APC UPS Battery Charge High Precision';
    $type = 'apc';
    [$oid,$current] = explode(' ', $oids);

    $precision = 10;
    $sensorType = 'apc';
    $current_oid = '.1.3.6.1.4.1.318.1.1.1.2.3.1.0';
    $index = 0;
    $current_val = ($current / $precision);
    $limit = 100;
    $lowlimit = 0;
    $warnlimit = 10;
    $descr = 'Battery Charge';

    discover_sensor($valid['sensor'], 'charge', $device, $current_oid, $index, $sensorType, $descr, $precision, 1, $lowlimit, $warnlimit, null, $limit, $current_val);
} else {
    // Try to just get capacity
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.2.2.1.0', '-OsqnU');
    d_echo($oids . "\n");

    if (! empty($oids)) {
        echo 'APC UPS Battery Charge';
        $type = 'apc';
        [$oid,$current] = explode(' ', $oids);

        $precision = 1;
        $sensorType = 'apc';
        $current_oid = '.1.3.6.1.4.1.318.1.1.1.2.2.1.0';
        $index = 0;
        $current_val = $current;
        $limit = 100;
        $lowlimit = 0;
        $warnlimit = 10;
        $descr = 'Battery Charge';

        discover_sensor($valid['sensor'], 'charge', $device, $current_oid, $index, $sensorType, $descr, $precision, 1, $lowlimit, $warnlimit, null, $limit, $current_val);
    }
}//end if
