<?php
$pwr_board = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.719.1.9.14');

/*
* False == OID not found - this is not an error.
* null  == timeout or something else that caused an error.
*/
if (is_null($pwr_board)) {
    echo "Error\n";
} else {
    $index = 1;
    
    // Board Input Voltage
    $oid = '.1.3.6.1.4.1.9.9.719.1.9.14.1.12';
    $description = "MB Input Voltage";
    d_echo($oid." - ".$description." - ".$pwr_board[$oid][$index]."\n");
    discover_sensor($valid['sensor'], 'voltage', $device, $oid.".".$index, 'mb-input-voltage', 'cimc', $description, '1', '1', null, null, null, null, $temp_board[$oid][$index]);
}
