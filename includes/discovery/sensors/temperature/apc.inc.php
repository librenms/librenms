<?php

// upsHighPrecBatteryTemperature
$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.2.3.2.0', '-OsqnU', '');
d_echo($oids . "\n");
$precision = 10;

if (! $oids) {
    // upsAdvBatteryTemperature, used in case high precision is not available
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.2.2.2.0', '-OsqnU', '');
    d_echo($oids . "\n");
    $precision = 1;
}

if ($oids) {
    echo 'APC UPS Internal ';
    [$oid,$current] = explode(' ', $oids);
    $sensorType = 'apc';
    $index = 0;
    $descr = 'Internal Temperature';

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current / $precision);
}

// Environmental monitoring on UPSes etc
$apc_env_data = snmpwalk_cache_oid($device, 'uioSensor', [], 'PowerNet-MIB', null, '-OQUse');
if ($apc_env_data) {
    // NMC2/NMC3/etc. Universal Input Output
    foreach (array_keys($apc_env_data) as $index) {
        $current = $apc_env_data[$index]['uioSensorStatusTemperatureDegC'];
        if ($current > 0) {
            // Temperature <= 0 -> Sensor not available
            $descr = $apc_env_data[$index]['uioSensorConfigSensorName'];
            $sensorType = 'apc';
            $oid = '.1.3.6.1.4.1.318.1.1.25.1.2.1.6.' . $index;

            // APC enum disabled(1), enabled(2)
            $low_limit = ($apc_env_data[$index]['uioSensorConfigMinTemperatureEnable'] != 1 ? $apc_env_data[$index]['uioSensorConfigMinTemperatureThreshold'] : null);
            $low_warn_limit = ($apc_env_data[$index]['uioSensorConfigLowTemperatureEnable'] != 1 ? $apc_env_data[$index]['uioSensorConfigLowTemperatureThreshold'] : null);
            $high_warn_limit = ($apc_env_data[$index]['uioSensorConfigHighTemperatureEnable'] != 1 ? $apc_env_data[$index]['uioSensorConfigHighTemperatureThreshold'] : null);
            $high_limit = ($apc_env_data[$index]['uioSensorConfigMaxTemperatureEnable'] != 1 ? $apc_env_data[$index]['uioSensorConfigMaxTemperatureThreshold'] : null);

            // universalInputOutput sensor entries all have an sub-index, presumably to allow for multiple sensors in the
            // future. Here we remove the sub-index from the first entry, so 1.1 becomes 1, 2.1 becomes 2, etc. However any
            // future appearing sub-index will remain untouched, so 1.2 will stay 1.2, 2.2 will stay 2.2, etc.
            // The reason that we remove the sub-index from the first entry is to preserve compatibility with sensors
            // created by prior versions using the legacy iemConfig and iemStatus tables.
            $split_index = explode('.', $index);
            if (count($split_index) == 2 && $split_index[1] == 1) {
                $index = $split_index[0];
            }
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, 1, 1, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
        }
    }
} else {
    // NMC1 Integrated Environmental Monitor (legacy)
    $apc_env_data = snmpwalk_cache_oid($device, 'iemConfigProbesTable', [], 'PowerNet-MIB', null, '-OQUse');
    $apc_env_data = snmpwalk_cache_oid($device, 'iemStatusProbesTable', $apc_env_data, 'PowerNet-MIB', null, '-OQUse');

    foreach (array_keys($apc_env_data) as $index) {
        // APC connected(2), disconnected(1)
        if ($apc_env_data[$index]['iemStatusProbeStatus'] != 1) {
            $descr = $apc_env_data[$index]['iemStatusProbeName'];
            $current = $apc_env_data[$index]['iemStatusProbeCurrentTemp'];
            $sensorType = 'apc';
            $oid = '.1.3.6.1.4.1.318.1.1.10.2.3.2.1.4.' . $index;
            // APC enum disabled(1), enabled(2)
            $low_limit = ($apc_env_data[$index]['iemConfigProbeMinTempEnable'] != 1 ? $apc_env_data[$index]['iemConfigProbeMinTempThreshold'] : null);
            $low_warn_limit = ($apc_env_data[$index]['iemConfigProbeLowTempEnable'] != 1 ? $apc_env_data[$index]['iemConfigProbeLowTempThreshold'] : null);
            $high_warn_limit = ($apc_env_data[$index]['iemConfigProbeHighTempEnable'] != 1 ? $apc_env_data[$index]['iemConfigProbeHighTempThreshold'] : null);
            $high_limit = ($apc_env_data[$index]['iemConfigProbeMaxTempEnable'] != 1 ? $apc_env_data[$index]['iemConfigProbeMaxTempThreshold'] : null);

            if ($current > 0) {
                // Temperature = 0 -> Sensor not available
                discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, 1, 1, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
            }
        }
    }
}

$apc_env_data = snmpwalk_cache_oid($device, 'emsProbeStatus', [], 'PowerNet-MIB');

foreach (array_keys($apc_env_data) as $index) {
    if ($apc_env_data[$index]['emsProbeStatusProbeCommStatus'] != 'commsNeverDiscovered') {
        $descr = $apc_env_data[$index]['emsProbeStatusProbeName'];
        $current = $apc_env_data[$index]['emsProbeStatusProbeTemperature'];
        $sensorType = 'apc';
        $oid = '.1.3.6.1.4.1.318.1.1.10.3.13.1.1.3.' . $index;
        $low_limit = $apc_env_data[$index]['emsProbeStatusProbeMinTempThresh'];
        $low_warn_limit = $apc_env_data[$index]['emsProbeStatusProbeLowTempThresh'];
        $high_warn_limit = $apc_env_data[$index]['emsProbeStatusProbeHighTempThresh'];
        $high_limit = $apc_env_data[$index]['emsProbeStatusProbeMaxTempThresh'];

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
    }
}

// InRow Chiller.
// A silly check to find out if it's the right hardware.
$oids = snmp_get($device, 'airIRRCGroupSetpointsCoolMetric.0', '-OsqnU', 'PowerNet-MIB');
if ($oids) {
    echo 'APC InRow Chiller ';
    $temps = [];
    $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.7'] = 'Rack Inlet'; //airIRRCUnitStatusRackInletTempMetric
    $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.9'] = 'Supply Air'; //airIRRCUnitStatusSupplyAirTempMetric
    $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.11'] = 'Return Air'; //airIRRCUnitStatusReturnAirTempMetric
    $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.24'] = 'Entering Fluid'; //airIRRCUnitStatusEnteringFluidTemperatureMetric
    $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.26'] = 'Leaving Fluid'; //airIRRCUnitStatusLeavingFluidTemperatureMetric
    foreach ($temps as $obj => $descr) {
        $oids = snmp_get($device, $obj . '.0', '-OsqnU', 'PowerNet-MIB');
        [$oid,$current] = explode(' ', $oids);
        $divisor = 10;
        $sensorType = substr($descr, 0, 2);
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', $sensorType, $descr, $divisor, '1', null, null, null, null, $current);
    }
}

// Portable Air Conditioner
$set_oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.13.2.2.4.0', '-OsqnU', '');

$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.13.2.2.10.0', '-OsqnU', '');
d_echo($oids . "\n");
d_echo($set_oids . "\n");

if ($oids !== false) {
    echo 'APC Portable Supply Temp ';
    [$oid,$current_raw] = explode(' ', $oids);
    $precision = 10;
    $current = ($current_raw / $precision);
    $sensorType = 'apc';
    $index = 0;
    if ($set_oids !== false) {
        [, $set_point_raw] = explode(' ', $set_oids);
        $set_point = ($set_point_raw / $precision);
        $descr = 'Supply Temp - Setpoint: ' . $set_point . '&deg;C';
    } else {
        $descr = 'Supply Temperature';
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
}

unset($oids);
$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.13.2.2.12.0', '-OsqnU', '');
d_echo($oids . "\n");

if ($oids !== false) {
    echo 'APC Portable Return Temp ';
    [$oid,$current_raw] = explode(' ', $oids);
    $precision = 10;
    $current = ($current_raw / $precision);
    $sensorType = 'apc';
    $index = 1;
    if ($set_oids !== false) {
        [, $set_point_raw] = explode(' ', $set_oids);
        $set_point = ($set_point_raw / $precision);
        $descr = 'Return Temp - Setpoint: ' . $set_point . '&deg;C';
    } else {
        $descr = 'Return Temperature';
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
}

unset($oids);
$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.13.2.2.14.0', '-OsqnU', '');
d_echo($oids . "\n");
if ($oids !== false) {
    echo 'APC Portable Remote Temp ';
    [$oid,$current_raw] = explode(' ', $oids);
    $precision = 10;
    $current = ($current_raw / $precision);
    $sensorType = 'apc';
    $index = 2;
    if ($set_oids !== false) {
        [, $set_point_raw] = explode(' ', $set_oids);
        $set_point = ($set_point_raw / $precision);
        $descr = 'Remote Temp - Setpoint: ' . $set_point . '&deg;C';
    } else {
        $descr = 'Remote Temperature';
    }

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
}

$cooling_unit = snmpwalk_cache_oid($device, 'coolingUnitExtendedAnalogEntry', [], 'PowerNet-MIB');
foreach ($cooling_unit as $index => $data) {
    $cur_oid = '.1.3.6.1.4.1.318.1.1.27.1.6.1.2.1.3.' . $index;
    $descr = $data['coolingUnitExtendedAnalogDescription'];
    $scale = $data['coolingUnitExtendedAnalogScale'];
    $value = $data['coolingUnitExtendedAnalogValue'];
    if (preg_match('/Temperature/', $descr) && $data['coolingUnitExtendedAnalogUnits'] == 'C' && $value >= 0) {
        discover_sensor($valid['sensor'], 'temperature', $device, $cur_oid, $cur_oid, 'apc', $descr, $scale, 1, null, null, null, null, $value);
    }
}

foreach ($pre_cache['cooling_unit_analog'] as $index => $data) {
    $cur_oid = '.1.3.6.1.4.1.318.1.1.27.1.4.1.2.1.3.' . $index;
    $descr = $data['coolingUnitStatusAnalogDescription'];
    $scale = $data['coolingUnitStatusAnalogScale'] ?? null;
    $value = $data['coolingUnitStatusAnalogValue'] ?? null;
    if (preg_match('/Temperature/', $descr) && $data['coolingUnitStatusAnalogUnits'] == 'C' && $value >= 0) {
        discover_sensor($valid['sensor'], 'temperature', $device, $cur_oid, $cur_oid, 'apc', $descr, $scale, 1, null, null, null, null, $value);
    }
}

foreach ($pre_cache['mem_sensors_status'] as $index => $data) {
    $cur_oid = '.1.3.6.1.4.1.318.1.1.10.4.2.3.1.5.' . $index;
    $descr = $data['memSensorsStatusSensorName'] . ' - ' . $data['memSensorsStatusSensorLocation'];
    $divisor = 1;
    $multiplier = 1;
    $value = $data['memSensorsTemperature'];
    if (is_numeric($value)) {
        $user_func = null;
        if ($pre_cache['memSensorsStatusSysTempUnits'] === 'fahrenheit') {
            $user_func = 'fahrenheit_to_celsius';
        }
        discover_sensor($valid['sensor'], 'temperature', $device, $cur_oid, 'memSensorsTemperature.' . $index, 'apc', $descr, $divisor, $multiplier, null, null, null, null, $value, 'snmp', null, null, $user_func);
    }
}
