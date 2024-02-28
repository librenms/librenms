<?php

/**
 * For ZTT MSJ devices
 */

// AC input phase A RED Voltage LINE 1
$inputVoltage1 = trim(snmp_get($device, '.1.3.6.1.4.1.49692.1.2.1.1.3.1', '-OsqnU'), '" ');
if (! empty($inputVoltage1)) {
    $divisor = 1000;
    $index = 0;
    $oid = '.1.3.6.1.4.1.49692.1.2.1.1.3.1';
    $descr = 'L1-RED-Voltage';
    $type = 'ACinputphase';
    $currentValue = $inputVoltage1 / $divisor;
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}
// AC input phase B YELLOW Voltage LINE 2
$inputVoltage2 = trim(snmp_get($device, '.1.3.6.1.4.1.49692.1.2.1.1.4.1', '-OsqnU'), '" ');
if (! empty($inputVoltage2)) {
    $divisor = 1000;
    $index = 1;
    $oid = '.1.3.6.1.4.1.49692.1.2.1.1.4.1';
    $descr = 'L2-YELLOW-Voltage';
    $type = 'ACinputphase';
    $currentValue = $inputVoltage2 / $divisor;
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}
// AC input phase C BLUE Voltage LINE 3
$inputVoltage3 = trim(snmp_get($device, '.1.3.6.1.4.1.49692.1.2.1.1.5.1', '-OsqnU'), '" ');
if (! empty($inputVoltage3)) {
    $divisor = 1000;
    $index = 2;
    $oid = '.1.3.6.1.4.1.49692.1.2.1.1.5.1';
    $descr = 'L3-BLUE-Voltage';
    $type = 'ACinputphase';
    $currentValue = $inputVoltage3 / $divisor;
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}
// DC output voltage
$onputVoltage1 = trim(snmp_get($device, '.1.3.6.1.4.1.49692.1.1.1.1.3.1', '-OsqnU'));
if (! empty($onputVoltage1)) {
    $divisor = 1000;
    $index = 3;
    $oid = '.1.3.6.1.4.1.49692.1.1.1.1.3.1';
    $descr = 'DCoutputvoltage';
    $type = 'DCoutput';
    $currentValue = $onputVoltage1 / $divisor;
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}
