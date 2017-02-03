<?php

echo 'MIKROTIK-MIB  ';
// Power Usage
$descr   = "Power Usage";
$oid     = ".1.3.6.1.4.1.14988.1.1.3.12.0"; // MIKROTIK-MIB::mtxrHlPower
$value   = snmp_get($device, $oid, '-Oqv');
$divisor = "10";

if (is_numeric($value)) {
    discover_sensor($valid['sensor'], 'power', $device, $oid, '1', 'power', $descr, $divisor, 1, null, null, null, null, $value / $divisor);
}
