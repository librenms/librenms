<?php

$oids = snmpwalk_cache_oid($device, 'tempHumidSensorEntry', [], 'Sentry3-MIB');
$divisor = '1';
$multiplier = '1';
d_echo($oids);

if ($oids) {
    echo 'ServerTech Sentry3 Humidity ';

    foreach ($oids as $sensor_index => $data) {
        // tempHumidSensorHumidValue
        $humidity_oid = '.1.3.6.1.4.1.1718.3.2.5.1.10.' . $sensor_index;
        $descr = 'Removable Sensor ' . $data['tempHumidSensorID'];
        $low_warn_limit = null;
        $low_limit = $data['tempHumidSensorHumidLowThresh'];
        $high_warn_limit = null;
        $high_limit = $data['tempHumidSensorHumidHighThresh'];
        $current = $data['tempHumidSensorHumidValue'];

        if (is_numeric($current) && $current >= 0) {
            discover_sensor($valid['sensor'], 'humidity', $device, $humidity_oid, 'tempHumidSensorHumidValue' . $sensor_index, 'sentry3', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
        }
    }
}

unset($oids);
