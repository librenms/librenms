<?php

// Adapated from Bluecoat sgos discovery
echo 'Benu Sensors ';
$sensor_index = 0;

$data = snmp_get_multi($device, ['benuSensorName.1.10', 'benuSensorName.1.11', 'benuSensorValue.1.10', 'benuSensorValue.1.11'], '-OQUs', 'BENU-CHASSIS-MIB');

$sensor_index = 0;
for ($index = 10; $index <= 11; $index++) { //Benu Voltage Sensors are index 10 and 11
    $sensor_oid = ".1.3.6.1.4.1.39406.1.1.1.4.1.1.5.1.$index";
    $descr = $data["1.$index"]['benuSensorName'];
    $current = $data["1.$index"]['benuSensorValue'];
    discover_sensor($valid['sensor'], 'voltage', $device, $sensor_oid, $sensor_index, 'benuos', $descr, '1', '1', null, null, null, null, $current);
    $sensor_index++;
}//end loop
