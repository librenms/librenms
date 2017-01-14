<?php

if ($device['os'] == 'routeros') {
    echo 'MIKROTIK-MIB ';
  
    $input_oid = '.1.3.6.1.4.1.14988.1.1.3.13.0'; // MIKROTIK-MIB::mtxrHlCurrent
    $input_value = snmp_get($device, $input_oid, '-Oqv');
    $descr = 'Current';
    $divisor = 1000;
    
    if (is_numeric($input_value) && $input_value > 0) {
        discover_sensor($valid['sensor'], 'current', $device, $input_oid, 0, $device['os'], $descr, $divisor, 1, null, null, null, null, $input_value / $divisor);
    }
}
