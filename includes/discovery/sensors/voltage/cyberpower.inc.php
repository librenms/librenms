<?php

// Input voltage
$oids = snmp_get($device, '.1.3.6.1.4.1.3808.1.1.3.2.3.1.1.6.1', '-OsqnU');
d_echo($oids . "\n");

if ($oids) {
    echo ' Cyberpower Input Voltage';
    [$oid, $voltage] = explode(' ', $oids);
    $divisor = 10;
    $type = 'cyberpower';
    $descr = 'Input';
    $voltage = $voltage / 10;
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, '0', $type, $descr, $divisor, '1', null, null, null, null, $voltage);
}
