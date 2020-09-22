<?php

$data = [];
$descr = '';

$temperature = snmp_get($device, '.1.3.6.1.4.1.89.53.15.1.9.1', '-Oqv');
if (is_numeric($temperature) && $temperature > '0') {
    $descr = 'Chassis Temperature';
    discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.89.53.15.1.9.1', '1', 'alcatel-device', $descr, '1', '1', null, null, null, null, $temperature);
}
