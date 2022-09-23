<?php

// input current
$oids = snmp_get($device, '.1.3.6.1.4.1.3808.1.1.3.2.3.1.1.2.1', '-OsqnU');
d_echo($oids . "\n");

if ($oids) {
    echo ' Cyberpower Input Current';
    [$oid, $current] = explode(' ', $oids);
    $divisor = 10;
    $type = 'cyberpower';
    $descr = 'Input';
    $current = $current / 10;
    discover_sensor($valid['sensor'], 'current', $device, $oid, '0', $type, $descr, $divisor, '1', null, null, null, null, $current);
}
