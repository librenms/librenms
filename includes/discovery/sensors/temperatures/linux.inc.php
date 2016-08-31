<?php
/*
 * cpu temp for raspberry pi
 * requires snmp extend agent script from librenms-agent
 */

$raspberry = snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv');

if (preg_match("/(bcm).+(boardrev)/", $raspberry)) {
    $sensor_type = "raspberry_temp";
    $sensor_oid = ".1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121.1";
    $descr = "CPU Temp";
    $value = snmp_get($device, $sensor_oid, '-Oqve');
    if ($value > 0) {
        discover_sensor($valid['sensor'], 'temperature', $device, $sensor_oid, 1, $sensor_type, $descr, 1, 1, null, null, null, null, $value);
    }
}
