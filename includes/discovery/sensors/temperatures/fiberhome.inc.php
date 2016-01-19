<?php
if ($device['os'] == 'fiberhome') {
    $temperature = snmp_get($device, 'sysTemperature.0', '-Oqv', 'GEPON-OLT-COMMON-MIB');
    if (is_numeric($temperature)) {
        $oid = '.1.3.6.1.4.1.5875.800.3.9.4.5.0';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', 'fiberhome', 'Internal Temperature', '1', '1', '20', null, null, '50', $temperature);
    }
}
