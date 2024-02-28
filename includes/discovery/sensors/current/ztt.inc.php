<?php

/**
 * For ZTT MSJ devices
 */

// AC input phase A RED current LINE-1
$oids1 = snmp_get($device, '.1.3.6.1.4.1.49692.1.2.1.1.6.1', '-OsqnU');

if ($oids1) {
    [$oid, $current] = explode(' ', $oids1);
    $divisor = 1000;
    $type = 'ACinputphase';
    $descr = 'L1-RED-Current';
    $current = $current;
    discover_sensor($valid['sensor'], 'current', $device, $oid, '0', $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// AC input phase B YELLOW current LINE-2
$oids2 = snmp_get($device, '.1.3.6.1.4.1.49692.1.2.1.1.7.1', '-OsqnU');

if ($oids2) {
    [$oid, $current] = explode(' ', $oids2);
    $divisor = 1000;
    $type = 'ACinputphase';
    $descr = 'L2-YELLOW-Current';
    $current = $current;
    discover_sensor($valid['sensor'], 'current', $device, $oid, '1', $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// AC input phase C BLUE current LINE-3
$oids3 = snmp_get($device, '.1.3.6.1.4.1.49692.1.2.1.1.8.1', '-OsqnU');

if ($oids3) {
    [$oid, $current] = explode(' ', $oids3);
    $divisor = 1000;
    $type = 'ACinputphase';
    $descr = 'L3-BLUE-Current';
    $current = $current;
    discover_sensor($valid['sensor'], 'current', $device, $oid, '2', $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// Total Load Current
$oids4 = snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.4.1', '-OsqnU');

if ($oids4) {
    [$oid, $current] = explode(' ', $oids4);
    $divisor = 1000;
    $type = 'Totalload';
    $descr = 'totalloadcurrent';
    $current = $current;
    discover_sensor($valid['sensor'], 'current', $device, $oid, '3', $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// Total Battery Current
$oids6 = snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.15.1', '-OsqnU');

if ($oids6) {
    [$oid, $current] = explode(' ', $oids6);
    $divisor = 1000;
    $type = 'Battery';
    $descr = 'totalbatterycurrent';
    $current = $current;
    discover_sensor($valid['sensor'], 'current', $device, $oid, '4', $type, $descr, $divisor, '1', null, null, null, null, $current);
}
