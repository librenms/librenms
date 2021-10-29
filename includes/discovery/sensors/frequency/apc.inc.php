<?php

$oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.8.5.3.2.1.4', '-OsqnU', '');
d_echo($oids . "\n");

if ($oids) {
    echo 'APC In ';
}

$divisor = 1;
$type = 'apc';
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$current] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $oid = '.1.3.6.1.4.1.318.1.1.8.5.3.2.1.4.' . $index;
        $descr = 'Input Feed ' . chr(64 + $index);
        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "3.2.1.4.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}

$oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.8.5.4.2.1.4', '-OsqnU', '');
d_echo($oids . "\n");

if ($oids) {
    echo ' APC Out ';
}

$divisor = 1;
$type = 'apc';
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$current] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 3)];
        $oid = '.1.3.6.1.4.1.318.1.1.8.5.4.2.1.4.' . $index;
        $descr = 'Output Feed';
        if (count(explode("\n", $oids)) > 1) {
            $descr .= " $index";
        }

        discover_sensor($valid['sensor'], 'frequency', $device, $oid, "4.2.1.4.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}

// upsHighPrecInputFrequency
$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.3.3.4.0', '-OsqnU', '');
d_echo($oids . "\n");
$divisor = 10;
$index = '3.3.4.0';

if (! $oids) {
    // upsAdvInputFrequency, used in case high precision is not available
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.3.2.4.0', '-OsqnU', '');
    d_echo($oids . "\n");
    $divisor = 1;
    $index = '3.2.4.0';
}

if ($oids) {
    echo ' APC In ';
    [$oid,$current] = explode(' ', $oids);
    $type = 'apc';
    $descr = 'Input';
    discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current / $divisor);
}

// upsHighPrecOutputFrequency
$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.4.3.2.0', '-OsqnU', '');
d_echo($oids . "\n");
$divisor = 10;
$index = '4.3.2.0';

if (! $oids) {
    // upsAdvOutputFrequency, used in case high precision is not available
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.4.2.2.0', '-OsqnU', '');
    d_echo($oids . "\n");
    $divisor = 1;
    $index = '4.2.2.0';
}

if ($oids) {
    echo ' APC Out ';
    [$oid,$current] = explode(' ', $oids);
    $type = 'apc';
    $descr = 'Output';
    discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current / $divisor);
}
