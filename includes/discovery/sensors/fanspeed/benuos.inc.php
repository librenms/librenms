<?php

// Adapated from Bluecoat sgos discovery

echo 'Benu Sensors ';
$sensor_index = 0;
for ($index = 4; $index <= 9; $index++) { //Benu Fans are index 4 thru 9
    $descr_oid   = "benuSensorName.1.$index";
    $fan_oid     = ".1.3.6.1.4.1.39406.1.1.1.4.1.1.5.1.$index";
    $descr       = snmp_get($device, $descr_oid, '-Oqv', 'BENU-CHASSIS-MIB');
    $current     = snmp_get($device, $fan_oid, '-Oqv', 'BENU-CHASSIS-MIB');
    discover_sensor($valid['sensor'], 'fanspeed', $device, $fan_oid, $sensor_index, 'benuos', $descr, '1', '1', null, null, null, null, $current);
    $sensor_index++;
}//end loop
