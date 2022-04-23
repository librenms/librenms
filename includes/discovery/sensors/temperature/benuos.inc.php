<?php

// Adapated from Bluecoat sgos discovery

echo 'Benu Sensors ';

$data = snmp_get_multi($device, ['benuSensorName.1.1', 'benuSensorName.1.2', 'benuSensorName.1.3', 'benuSensorValue.1.1', 'benuSensorValue.1.2', 'benuSensorValue.1.3'], '-OQUs', 'BENU-CHASSIS-MIB');

$sensor_index = 0;
for ($index = 1; $index <= 3; $index++) { //Benu Temp Sensors are index 1 thru 3
    $sensor_oid = ".1.3.6.1.4.1.39406.1.1.1.4.1.1.5.1.$index";
    $descr = $data["1.$index"]['benuSensorName'];
    $current = $data["1.$index"]['benuSensorValue'];
    discover_sensor($valid['sensor'], 'temparature', $device, $sensor_oid, $sensor_index, 'benuos', $descr, '1', '1', null, null, null, null, $current);
    $sensor_index++;
}//end loop
