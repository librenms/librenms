<?php

$text_oid = 'rbSysVoltage';
$num_oid = '.1.3.6.1.4.1.31926.1.1';
$sensor_index = 0;

$oids = snmp_walk($device, $text_oid . '.' . $sensor_index, '-OsqnU', 'RADIO-BRIDGE-MIB');
d_echo($oids . "\n");

if (! empty($oids)) {
    echo 'Siklu Voltage ';

    $type = 'siklu';
    if (! empty($oids)) {
        [,$current] = explode(' ', $oids);
        $descr = 'System voltage';
        discover_sensor($valid['sensor'], 'voltage', $device, $num_oid . '.' . $sensor_index, $text_oid . '.' . $sensor_index, $type, $descr, 1, 1, null, null, null, null, $current);
    }
}
