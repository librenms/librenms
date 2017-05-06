<?php

$temperature = snmp_get($device, '.1.3.6.1.4.1.30631.1.9.1.1.4.0', '-Oqv');
if (is_numeric($temperature)) {
    $oid = '.1.3.6.1.4.1.30631.1.9.1.1.4.0';
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', 'sti410c', 'Internal Temperature', 1, 1, null, null, null, null, $temperature);
}
