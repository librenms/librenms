<?php

// input current
$oids = snmp_get($device, '.1.3.6.1.4.1.17420.1.2.9.1.11.0', '-OsqnU');
d_echo($oids . "\n");

if ($oids) {
    echo ' Cyberpower Input Current';
    [$oid, $current] = explode(' ', $oids);
    $divisor = 10;
    $type = 'digipower';
    $descr = 'Input';
    $current = $current / 10;
    discover_sensor($valid['sensor'], 'current', $device, $oid, 0, $type, $descr, $divisor, 1, null, null, null, null, $current);
}
