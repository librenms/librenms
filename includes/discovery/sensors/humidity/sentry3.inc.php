<?php

if ($device['os'] == 'sentry3') {
    $oids       = snmp_walk($device, 'tempHumidSensorHumidValue', '-Osqn', 'Sentry3-MIB');
    $divisor    = '1';
    $multiplier = '1';
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'ServerTech Sentry ';
    }

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $index            = $split_oid[(count($split_oid) - 1)];

            // tempHumidSensorHumidValue
            $humidity_oid    = '1.3.6.1.4.1.1718.3.2.5.1.10.1.'.$index;
            $descr           = 'Removable Sensor '.$index;
            $low_warn_limit  = '0';
            $low_limit       = snmp_get($device, "tempHumidSensorHumidLowThresh.1.$index", '-Ovq', 'Sentry3-MIB');
            $high_warn_limit = '0';
            $high_limit      = snmp_get($device, "tempHumidSensorHumidHighThresh.1.$index", '-Ovq', 'Sentry3-MIB');
            $current         = snmp_get($device, "$humidity_oid", '-Ovq', 'Sentry3-MIB');

            if ($current >= 0) {
                discover_sensor($valid['sensor'], 'humidity', $device,
                    $humidity_oid, $index, 'sentry3',
                    $descr, $divisor, $multiplier, $low_limit, $low_warn_limit,
                    $high_warn_limit, $high_limit, $current);
            }
        }

        unset($data);
    }

    unset($oids);
}
