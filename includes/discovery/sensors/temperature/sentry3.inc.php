<?php
// TODO use pre-cache prevent redundant snmp reads
$oids = snmp_walk($device, 'tempHumidSensorTempValue', '-Osqn', 'Sentry3-MIB');
d_echo($oids."\n");

$oids       = trim($oids);
$divisor    = '10';
$multiplier = '1';
if ($oids) {
    echo 'ServerTech Sentry3 Temperature ';

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $index            = substr($oid, -3);        

            // tempHumidSensorTempValue
            $temperature_oid = '.1.3.6.1.4.1.1718.3.2.5.1.6.'.$index;
            $descr           = 'Removable Sensor '.$index;
            $low_warn_limit  = null;
            $low_limit       = snmp_get($device, "tempHumidSensorTempLowThresh.$index", '-OQUnv', 'Sentry3-MIB');
            $high_warn_limit = null;
            $high_limit      = snmp_get($device, "tempHumidSensorTempHighThresh.$index", '-OQUnv', 'Sentry3-MIB');
            $current         = (snmp_get($device, "$temperature_oid", '-OvqU', 'Sentry3-MIB') / $divisor);

            $sentry_temp_scale = snmp_get($device, "tempHumidSensorTempScale.$index", '-Ovq', 'Sentry3-MIB');
            // TODO passing user func does not convert limits only value
            if ($sentry_temp_scale == 'fahrenheit') {
                $low_limit = fahrenheit_to_celsius($low_limit, $sentry_temp_scale);
                $high_limit = fahrenheit_to_celsius($high_limit, $sentry_temp_scale);
                $current = fahrenheit_to_celsius($current, $sentry_temp_scale);
            }

            if (is_numeric($current) && $current >= 0) {
                d_echo("current temp value $current $user_func");
                discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_oid, 'sentry3', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current, 'snmp', null, null, null);
            }
        }
    }
}
