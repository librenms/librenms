<?php

$oid = '.1.3.6.1.4.1.31926.1.2.0';
$oids = snmp_get($device, "$oid", '-OsqnU');
d_echo($oids . "\n");

if (! empty($oids)) {
    echo 'Siklu Temperature ';
}

$divisor = 1;
$type = 'siklu';
if (! empty($oids)) {
    [,$current] = explode(' ', $oids);
    $index = $oid;
    $descr = 'System Temp';
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
