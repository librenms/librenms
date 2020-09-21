<?php

echo 'MGE UPS External ';

// Environmental monitoring on UPSes etc
// FIXME upsmgConfigEnvironmentTable and upsmgEnvironmentSensorTable are used but there are others ...
$mge_env_data = snmpwalk_cache_oid($device, 'upsmgConfigEnvironmentTable', [], 'MG-SNMP-UPS-MIB');
$mge_env_data = snmpwalk_cache_oid($device, 'upsmgEnvironmentSensorTable', $mge_env_data, 'MG-SNMP-UPS-MIB');

/**
 * upsmgConfigSensorIndex.1 = 1
 * upsmgConfigSensorName.1 = "Environment sensor"
 * upsmgConfigTemperatureLow.1 = 5
 * upsmgConfigTemperatureHigh.1 = 40
 * upsmgConfigTemperatureHysteresis.1 = 2
 * upsmgConfigHumidityLow.1 = 5
 * upsmgConfigHumidityHigh.1 = 90
 * upsmgConfigHumidityHysteresis.1 = 5
 * upsmgConfigInput1Name.1 = "Input #1"
 * upsmgConfigInput1ClosedLabel.1 = "closed"
 * upsmgConfigInput1OpenLabel.1 = "open"
 * upsmgConfigInput2Name.1 = "Input #2"
 * upsmgConfigInput2ClosedLabel.1 = "closed"
 * upsmgConfigInput2OpenLabel.1 = "open"
 *
 * upsmgEnvironmentIndex.1 = 1
 * upsmgEnvironmentComFailure.1 = no
 * upsmgEnvironmentTemperature.1 = 287
 * upsmgEnvironmentTemperatureLow.1 = no
 * upsmgEnvironmentTemperatureHigh.1 = no
 * upsmgEnvironmentHumidity.1 = 17
 * upsmgEnvironmentHumidityLow.1 = no
 * upsmgEnvironmentHumidityHigh.1 = no
 * upsmgEnvironmentInput1State.1 = open
 * upsmgEnvironmentInput2State.1 = open
 **/
foreach (array_keys($mge_env_data) as $index) {
    $descr = $mge_env_data[$index]['upsmgConfigSensorName'];
    $current = $mge_env_data[$index]['upsmgEnvironmentHumidity'];
    $sensorType = 'mge';
    $oid = '.1.3.6.1.4.1.705.1.8.7.1.6.' . $index;
    $low_limit = $mge_env_data[$index]['upsmgConfigHumidityLow'];
    $high_limit = $mge_env_data[$index]['upsmgConfigHumidityHigh'];
    $hysteresis = $mge_env_data[$index]['upsmgConfigHumidityHysteresis'];

    // FIXME warninglevels might need some other calculation in stead of hysteresis
    $low_warn_limit = ($low_limit + $hysteresis);
    $high_warn_limit = ($high_limit - $hysteresis);

    d_echo("low_limit : $low_limit\nlow_warn_limit : $low_warn_limit\nhigh_warn_limit : $high_warn_limit\nhigh_limit : $high_limit\n");

    if ($current != 0) {
        // Humidity = 0 -> Sensor not available
        // FIXME true for MGE as wel as APC?
        discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
    }
}//end foreach
