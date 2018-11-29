<?php

echo 'Vigintos';

$power_oid = '.1.3.6.1.4.1.27993.5.9.1.3.1.1'; //
$power_value = snmp_get($device, $power_oid, '-Oqv');
$descr = '(Modulator) Forward Power';
$divisor = 1000;

if (is_numeric($power_value) && $power_value > 0) {
    discover_sensor($valid['sensor'], 'power', $device, $power_oid, 0, $device['os'], $descr, $divisor, 1, null, null, null, null, $power_value / $divisor);
}

$power_oid = '.1.3.6.1.4.1.27993.5.9.1.3.2.1'; //
$power_value = snmp_get($device, $power_oid, '-Oqv');
$descr = '(Ampifier) Forward Power';
$divisor = 1;

if (is_numeric($power_value) && $power_value > 0) {
    discover_sensor($valid['sensor'], 'power', $device, $power_oid, 1, $device['os'], $descr, $divisor, 1, null, null, null, null, $power_value / $divisor);
}

$power_oid = '.1.3.6.1.4.1.27993.5.9.1.3.2.2'; //
$power_value = snmp_get($device, $power_oid, '-Oqv');
$descr = '(Ampifier) Reflected Power';
$divisor = 1;

if (is_numeric($power_value)) {
    discover_sensor($valid['sensor'], 'power', $device, $power_oid, 2, $device['os'], $descr, $divisor, 1, null, null, null, null, $power_value / $divisor);
}

$power_oid = '.1.3.6.1.4.1.27993.5.9.1.3.2.3'; //
$power_value = snmp_get($device, $power_oid, '-Oqv');
$descr = '(Ampifier)Driver Forward Power';
$divisor = 1;

if (is_numeric($power_value)) {
    discover_sensor($valid['sensor'], 'power', $device, $power_oid, 3, $device['os'], $descr, $divisor, 1, null, null, null, null, $power_value / $divisor);
}
