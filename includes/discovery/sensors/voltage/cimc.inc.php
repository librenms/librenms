<?php
$voltage = snmp_get($device, '.1.3.6.1.4.1.9.9.719.1.9.14.1.12.1', '-Oqv');

/*
* False == OID not found - this is not an error.
* null  == timeout or something else that caused an error.
*/
if (!empty($voltage)) {
    $index = 1;
    
    // Board Input Voltage
    $oid = '.1.3.6.1.4.1.9.9.719.1.9.14.1.12';
    $description = "MB Input Voltage";
    d_echo($oid." - ".$description." - ".$voltage."\n");
    discover_sensor($valid['sensor'], 'voltage', $device, $oid.".".$index, 'mb-input-voltage', 'cimc', $description, '1', '1', null, null, null, null, $voltage);
}
