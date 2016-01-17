<?php

if ($device['os'] == 'apc') {
    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.1.2.2.2.0', '-OsqnU', '');
    d_echo($oids."\n");

    if ($oids) {
        echo 'APC UPS Internal ';
        list($oid,$current) = explode(' ', $oids);
        $precision          = 1;
        $sensorType         = 'apc';
        $index              = 0;
        $descr              = 'Internal Temperature';

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', null, null, null, null, $current);
    }

    // Environmental monitoring on UPSes etc
    // FIXME emConfigProbesTable may also be used? But not filled out on my device...
    $apc_env_data = snmpwalk_cache_oid($device, 'iemConfigProbesTable', array(), 'PowerNet-MIB');
    $apc_env_data = snmpwalk_cache_oid($device, 'iemStatusProbesTable', $apc_env_data, 'PowerNet-MIB');

    foreach (array_keys($apc_env_data) as $index) {
        $descr           = $apc_env_data[$index]['iemStatusProbeName'];
        $current         = $apc_env_data[$index]['iemStatusProbeCurrentTemp'];
        $sensorType      = 'apc';
        $oid             = '.1.3.6.1.4.1.318.1.1.10.2.3.2.1.4.'.$index;
        $low_limit       = ($apc_env_data[$index]['iemConfigProbeMinTempEnable'] != 'disabled' ? $apc_env_data[$index]['iemConfigProbeMinTempThreshold'] : null);
        $low_warn_limit  = ($apc_env_data[$index]['iemConfigProbeLowTempEnable'] != 'disabled' ? $apc_env_data[$index]['iemConfigProbeLowTempThreshold'] : null);
        $high_warn_limit = ($apc_env_data[$index]['iemConfigProbeHighTempEnable'] != 'disabled' ? $apc_env_data[$index]['iemConfigProbeHighTempThreshold'] : null);
        $high_limit      = ($apc_env_data[$index]['iemConfigProbeMaxTempEnable'] != 'disabled' ? $apc_env_data[$index]['iemConfigProbeMaxTempThreshold'] : null);

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
    }

    /*
        [iemConfigProbeHighHumidThreshold] => -1
            [iemConfigProbeLowHumidThreshold] => -1
            [iemConfigProbeHighHumidEnable] => disabled
            [iemConfigProbeLowHumidEnable] => disabled
            [iemConfigProbeMaxHumidThreshold] => -1
            [iemConfigProbeMinHumidThreshold] => -1
            [iemConfigProbeMaxHumidEnable] => disabled
            [iemConfigProbeMinHumidEnable] => disabled
            [iemConfigProbeHumidHysteresis] => -1

            [iemStatusProbeStatus] => connected
            [iemStatusProbeCurrentTemp] => 25
            [iemStatusProbeTempUnits] => celsius

            [iemStatusProbeCurrentHumid] => 0
     */

    // InRow Chiller.
    // A silly check to find out if it's the right hardware.
    $oids = snmp_get($device, 'airIRRCGroupSetpointsCoolMetric.0', '-OsqnU', 'PowerNet-MIB');
    if ($oids) {
        echo 'APC InRow Chiller ';
        $temps = array();
        $temps['airIRRCUnitStatusRackInletTempMetric']            = 'Rack Inlet';
        $temps['airIRRCUnitStatusSupplyAirTempMetric']            = 'Supply Air';
        $temps['airIRRCUnitStatusReturnAirTempMetric']            = 'Return Air';
        $temps['airIRRCUnitStatusEnteringFluidTemperatureMetric'] = 'Entering Fluid';
        $temps['airIRRCUnitStatusLeavingFluidTemperatureMetric']  = 'Leaving Fluid';
        foreach ($temps as $obj => $descr) {
            $oids                   = snmp_get($device, $obj.'.0', '-OsqnU', 'PowerNet-MIB');
            list($oid,$current) = explode(' ', $oids);
            $divisor            = 10;
            $sensorType         = substr($descr, 0, 2);
            echo discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', $sensorType, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }

    // Portable Air Conditioner
    $set_oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.13.2.2.4.0', '-OsqnU', '');

    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.13.2.2.10.0', '-OsqnU', '');
    d_echo($oids."\n");
    d_echo($set_oids."\n");

    if ($oids !== false) {
        echo 'APC Portable Supply Temp ';
        list($oid,$current_raw) = explode(' ', $oids);
        $precision              = 10;
        $current                = ($current_raw / $precision);
        $sensorType             = 'apc';
        $index                  = 0;
        if ($set_oids !== false) {
            list(, $set_point_raw) = explode(' ', $set_oids);
            $set_point             = ($set_point_raw / $precision);
            $descr                 = 'Supply Temp - Setpoint: '.$set_point.'&deg;C';
        }
        else {
            $descr = 'Supply Temperature';
        }

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
    }

    unset($oids);
    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.13.2.2.12.0', '-OsqnU', '');
    d_echo($oids."\n");

    if ($oids !== false) {
        echo 'APC Portable Return Temp ';
        list($oid,$current_raw) = explode(' ', $oids);
        $precision              = 10;
        $current                = ($current_raw / $precision);
        $sensorType             = 'apc';
        $index                  = 1;
        if ($set_oids !== false) {
            list(, $set_point_raw) = explode(' ', $set_oids);
            $set_point             = ($set_point_raw / $precision);
            $descr                 = 'Return Temp - Setpoint: '.$set_point.'&deg;C';
        }
        else {
            $descr = 'Return Temperature';
        }

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
    }

    unset($oids);
    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.13.2.2.14.0', '-OsqnU', '');
    d_echo($oids."\n");
    if ($oids !== false) {
        echo 'APC Portable Remote Temp ';
        list($oid,$current_raw) = explode(' ', $oids);
        $precision              = 10;
        $current                = ($current_raw / $precision);
        $sensorType             = 'apc';
        $index                  = 2;
        if ($set_oids !== false) {
            list(, $set_point_raw) = explode(' ', $set_oids);
            $set_point             = ($set_point_raw / $precision);
            $descr                 = 'Remote Temp - Setpoint: '.$set_point.'&deg;C';
        }
        else {
            $descr = 'Remote Temperature';
        }

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
    }
}//end if
