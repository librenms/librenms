<?php

if ($device['os'] == 'sentry3') {
    $oids = snmp_walk($device, 'tempHumidSensorTempValue', '-Osqn', 'Sentry3-MIB');
    d_echo($oids."\n");

    $oids       = trim($oids);
    $divisor    = '10';
    $multiplier = '1';
    if ($oids) {
        echo 'ServerTech Sentry3 Temperature ';
    }

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $index            = $split_oid[(count($split_oid) - 1)];

            // tempHumidSensorTempValue
            $temperature_oid = '1.3.6.1.4.1.1718.3.2.5.1.6.1.'.$index;
            $descr           = 'Removable Sensor '.$index;
            $low_warn_limit  = null;
            $low_limit       = (snmp_get($device, "tempHumidSensorTempLowThresh.1.$index", '-Ovq', 'Sentry3-MIB') / $divisor);
            $high_warn_limit = null;
            $high_limit      = (snmp_get($device, "tempHumidSensorTempHighThresh.1.$index", '-Ovq', 'Sentry3-MIB') / $divisor);
            $current         = (snmp_get($device, "$temperature_oid", '-Ovq', 'Sentry3-MIB') / $divisor);

            if ($current >= 0) {
                discover_sensor($valid['sensor'], 'temperature', $device,
                    $temperature_oid, $index, 'sentry3',
                    $descr, $divisor, $multiplier, $low_limit, $low_warn_limit,
                    $high_warn_limit, $high_limit, $current);
            }
        }
    }
}
