<?php

$oids = snmpwalk_cache_oid($device, 'tempHumidSensorEntry', [], 'Sentry3-MIB');
d_echo($oids . "\n");
$divisor = '10';
$multiplier = '1';
if ($oids) {
    echo 'ServerTech Sentry3 Temperature ';

    foreach ($oids as $sensor_index => $data) {
        // tempHumidSensorTempValue
        $temperature_oid = '.1.3.6.1.4.1.1718.3.2.5.1.6.' . $sensor_index;
        $descr = 'Removable Sensor ' . $data['tempHumidSensorID'];
        $low_warn_limit = null;
        $low_limit = $data['tempHumidSensorTempLowThresh'];
        $high_warn_limit = null;
        $high_limit = $data['tempHumidSensorTempHighThresh'];
        $current = ($data['tempHumidSensorTempValue'] / $divisor);
        $sentry_temp_scale = $data['tempHumidSensorTempScale'];
        $user_func = null;
        if ($sentry_temp_scale == 'fahrenheit') {
            $low_limit = fahrenheit_to_celsius($low_limit, $sentry_temp_scale);
            $high_limit = fahrenheit_to_celsius($high_limit, $sentry_temp_scale);
            $user_func = 'fahrenheit_to_celsius';
            $current = fahrenheit_to_celsius($current, $sentry_temp_scale);
        }

        if (is_numeric($current) && $current >= 0) {
            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, 'tempHumidSensorTempValue' . $sensor_index, 'sentry3', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current, 'snmp', null, null, $user_func);
        }
    }
}
