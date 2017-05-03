<?php

$oids = snmp_walk($device, '.1.3.6.1.4.1.5528.100.4.1.1.1.4', '-Osqn', '');
d_echo($oids."\n");

$oids = trim($oids);
if ($oids) {
    echo 'NetBotz ';
    foreach (explode("\n", $oids) as $data) {
        list($oid,$descr) = explode(' ', $data, 2);
        $split_oid        = explode('.', $oid);
        $temperature_id   = $split_oid[(count($split_oid) - 1)];
        $temperature_oid  = ".1.3.6.1.4.1.5528.100.4.1.1.1.8.$temperature_id";
        $temperature      = snmp_get($device, $temperature_oid, '-Ovq');
        $descr            = str_replace('"', '', $descr);
        $descr            = trim($descr);
        if ($temperature != '0' && $temperature <= '1000') {
            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'netbotz', $descr, '1', '1', null, null, null, null, $temperature);
        }
    }
}

d_echo($pre_cache['netbotz_temperature']);

if (is_array($pre_cache['netbotz_temperature'])) {
    echo 'NetBotz ';
    foreach ($pre_cache['netbotz_temperature'] as $index => $data) {
        if ($data['dewPointSensorValue']) {
            $divisor = 10;
            $multiplier = 1;
            $value = $data['dewPointSensorValue'] / $divisor;
            $oid = '.1.3.6.1.4.1.5528.100.4.1.3.1.2.' . $index;
            $index = 'dewPointSensorValue.' . $index;
            $descr = $data['dewPointSensorLabel'];
            if (is_numeric($value)) {
                discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'netbotz', $descr, $divisor, $multiplier, null, null, null, null, $value);
            }
        }
    }
}
