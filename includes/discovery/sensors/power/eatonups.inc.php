<?php

echo 'EATON Power';
// Power Usage
$descr = 'Power Usage';
$oid = '.1.3.6.1.4.1.534.1.4.4.1.4.1'; //
$value = snmp_get($device, $oid, '-Oqv');
$divisor = '1';
if (is_numeric($value)) {
    $value = ($value / $divisor);
    discover_sensor($valid['sensor'], 'power', $device, $oid, '1', 'eatonups', $descr, $divisor, 1, null, null, null, null, $value); // No limits have been specified since all equipment is different and will use different amount of Watts
}
