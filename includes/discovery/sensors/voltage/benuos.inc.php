<?php

// Adapated from Bluecoat sgos discovery

echo 'Benu Sensors ';
$sensor_index = 0;
for ($index = 10; $index <= 11; $index++) { //Benu Voltage Sensors are index 1 thru 3
    $descr_oid   = "benuSensorName.1.$index";
    $volt_oid    = ".1.3.6.1.4.1.39406.1.1.1.4.1.1.5.1.$index";
    $descr       = snmp_get($device, $descr_oid, '-Oqv', 'BENU-CHASSIS-MIB');
    $current     = snmp_get($device, $volt_oid, '-Oqv', 'BENU-CHASSIS-MIB');
    discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $sensor_index, 'benuos', $descr, '1', '1', null, null, null, null, $current);
    $sensor_index++;
}//end loop
