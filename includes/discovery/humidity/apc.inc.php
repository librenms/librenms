<?php

if ($device['os'] == 'apc') {
    // Environmental monitoring on UPSes etc
    // FIXME emConfigProbesTable may also be used? But not filled out on my device...
    $apc_env_data = snmpwalk_cache_oid($device, 'iemConfigProbesTable', array(), 'PowerNet-MIB');
    $apc_env_data = snmpwalk_cache_oid($device, 'iemStatusProbesTable', $apc_env_data, 'PowerNet-MIB');

    foreach (array_keys($apc_env_data) as $index) {
        $descr           = $apc_env_data[$index]['iemStatusProbeName'];
        $current         = $apc_env_data[$index]['iemStatusProbeCurrentHumid'];
        $sensorType      = 'apc';
        $oid             = '.1.3.6.1.4.1.318.1.1.10.2.3.2.1.6.'.$index;
        $low_limit       = ($apc_env_data[$index]['iemConfigProbeMinHumidEnable'] != 'disabled' ? $apc_env_data[$index]['iemConfigProbeMinHumidThreshold'] : null);
        $low_warn_limit  = ($apc_env_data[$index]['iemConfigProbeLowHumidEnable'] != 'disabled' ? $apc_env_data[$index]['iemConfigProbeLowHumidThreshold'] : null);
        $high_warn_limit = ($apc_env_data[$index]['iemConfigProbeHighHumidEnable'] != 'disabled' ? $apc_env_data[$index]['iemConfigProbeHighHumidThreshold'] : null);
        $high_limit      = ($apc_env_data[$index]['iemConfigProbeMaxHumidEnable'] != 'disabled' ? $apc_env_data[$index]['iemConfigProbeMaxHumidThreshold'] : null);

        if ($current != 0) {
            // Humidity = 0 -> Sensor not available
            discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
        }
    }
}//end if
