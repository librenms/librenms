<?php

echo ' EXTREME-BASE-MIB ';
// Power Usage
$descr = 'Power Usage';
$oid = '.1.3.6.1.4.1.1916.1.1.1.40.1.0'; // extremeSystemPowerUsage
$value = snmp_get($device, $oid, '-Oqv', 'EXTREME-BASE-MIB');
$divisor = '1000';

if (is_numeric($value)) {
    $value = ($value / $divisor); // Nasty hack to divide the first value by 1000 since the divisor only works for polling after the sensor has been added
    discover_sensor($valid['sensor'], 'power', $device, $oid, '1', 'extreme-power', $descr, $divisor, 1, null, null, null, null, $value); // No limits have been specified since all equipment is different and will use different amount of Watts
}
