<?php

$temperature = snmp_get($device, 'c1Temperature.0', '-Oqv', 'L-AM3440-A-Private');

if(isHexString($temperature)){ # Loop provides the value as hex 
    $temperature = snmp_hexstring($temperature); # Convert from hex to string
    $temperature = preg_replace('/[^0-9]/', '', $temperature); #Remove everything thats not an integer
}

if (is_numeric($temperature) && $temperature > '0') {
    $descr = 'Chassis Temperature';
    discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.823.34441.1.10.11.0', '1', 'loop-telecom', $descr, '1', '1', null, null, null, null, $temperature);
}
