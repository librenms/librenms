<?php

$oids = snmp_walk($device, 'tempHumidSensorEntry', '-OQUs', 'Sentry3-MIB');
d_echo($oids."\n");

$oids       = trim($oids);
$divisor    = '10';
$multiplier = '1';
if ($oids) {
    echo 'ServerTech Sentry3 Temperature ';
    $index_table = array();
    $temp_table = array();
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$oid_val) = explode(' = ', $data, 2);
            $index            = substr($oid, -3);
            $temp_table[$oid] = $oid_val;
            if (!in_array($index, $index_table)){
                $index_table[] = $index;
            }
        }
    }

    foreach ($index_table as $sensor_index) {
        // tempHumidSensorTempValue
        $temperature_oid = '.1.3.6.1.4.1.1718.3.2.5.1.6.'.$sensor_index;
        $descr           = 'Removable Sensor '.$temp_table["tempHumidSensorID.$sensor_index"];
        $low_warn_limit  = null;
        $low_limit       = $temp_table["tempHumidSensorTempLowThresh.$sensor_index"];
        $high_warn_limit = null;
        $high_limit      = $temp_table["tempHumidSensorTempHighThresh.$sensor_index"];
        $current         = ($temp_table["tempHumidSensorTempValue.$sensor_index"] / $divisor);
        $sentry_temp_scale = $temp_table["tempHumidSensorTempScale.$sensor_index"];

        $user_func = null;
        if ($sentry_temp_scale == 'fahrenheit') {
            $low_limit = fahrenheit_to_celsius($low_limit, $sentry_temp_scale);
            $high_limit = fahrenheit_to_celsius($high_limit, $sentry_temp_scale);
            $user_func = 'fahrenheit_to_celsius';
            $current = fahrenheit_to_celsius($current, $sentry_temp_scale);
        }

        if (is_numeric($current) && $current >= 0) {
            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $sensor_index, 'sentry3', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current, 'snmp', null, null, $user_func);
        }
    }
}
