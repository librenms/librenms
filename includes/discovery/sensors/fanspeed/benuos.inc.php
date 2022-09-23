<?php

// Adapated from Bluecoat sgos discovery
echo 'Benu Sensors ';

$data = snmp_get_multi($device, ['benuSensorName.1.4', 'benuSensorName.1.5', 'benuSensorName.1.6', 'benuSensorName.1.7', 'benuSensorName.1.8', 'benuSensorName.1.9', 'benuSensorValue.1.4', 'benuSensorValue.1.5', 'benuSensorValue.1.6', 'benuSensorValue.1.7', 'benuSensorValue.1.8', 'benuSensorValue.1.9'], '-OQUs', 'BENU-CHASSIS-MIB');

$sensor_index = 0;
for ($index = 4; $index <= 9; $index++) { //Benu Fans are index 4 thru 9
    $sensor_oid = ".1.3.6.1.4.1.39406.1.1.1.4.1.1.5.1.$index";
    $descr = $data["1.$index"]['benuSensorName'];
    $current = $data["1.$index"]['benuSensorValue'];
    discover_sensor($valid['sensor'], 'fanspeed', $device, $sensor_oid, $sensor_index, 'benuos', $descr, '1', '1', null, null, null, null, $current);
    $sensor_index++;
}//end loop
