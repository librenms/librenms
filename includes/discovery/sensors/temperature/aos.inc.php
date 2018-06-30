<?php

echo 'Alcatel-Lucent Device: ';

$descr       = 'Chassis Temperature';
$temperature = snmp_get($device, '.1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.17.0', '-Oqv');

if ($descr != '' && is_numeric($temperature) && $temperature > '0') {
    $temperature_oid = '.1.3.6.1.4.1.18248.1.1.1.0';
    discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.17.0', '1', 'alcatel-device', $descr, '1', '1', null, null, null, null, $temperature);
}
