<?php
/*
 * LibreNMS - pfSense CPU Temperature Sensor Discovery
 * Adds support for CPU temperature monitoring via NET-SNMP extend OID
 */
if ($device['os'] === 'pfsense') {
    $temp_oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.7.99.112.117.84.101.109.112'; // NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."cpuTemp"
    $temp = snmp_get($device, $temp_oid, '-Oqv');
    if (is_numeric($temp)) {
        discover_sensor($valid['sensor'], 'temperature', $device, $temp_oid, 0, 'pfsense', 'CPU Temperature', 1, 1, null, null, null, null, $temp);
    }
}
