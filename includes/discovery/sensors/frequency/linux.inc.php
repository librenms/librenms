<?php
/*
 * raspberry pi frequencies
 * requires snmp extend agent script from librenms-agent
 */
$raspberry = snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv');

if (preg_match("/(bcm).+(boardrev)/", $raspberry)) {
    $sensor_type = "raspberry_freq";
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121.';

    for ($freq = 6; $freq < 8; $freq++) {
        switch ($freq) {
            case "6":
                $descr = "ARM";
                break;
            case "7":
                $descr = "Core";
                break;
        }
        $value = snmp_get($device, $oid.$freq, '-Oqve');
        if (is_numeric($value)) {
            discover_sensor($valid['sensor'], 'frequency', $device, $oid.$freq, $freq, $sensor_type, $descr, 1, 1, null, null, null, null, $value);
        }
    }
}
