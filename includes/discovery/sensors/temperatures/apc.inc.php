<?php

if ($device['os'] == 'apc') {
    echo 'APC ';
    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.1.2.2.2.0', '-OsqnU', '');

    if ($oids) {
        d_echo('Internal ');
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
    if (!empty($apc_env_data)) {
        $apc_env_data = snmpwalk_cache_oid($device, 'iemStatusProbesTable', $apc_env_data, 'PowerNet-MIB');
    }

    foreach ($apc_env_data as $index => $data) {
        $descr           = $data['iemStatusProbeName'];
        $current         = $data['iemStatusProbeCurrentTemp'];
        $sensorType      = 'apc';
        $oid             = '.1.3.6.1.4.1.318.1.1.10.2.3.2.1.4.'.$index;
        $low_limit       = ($data['iemConfigProbeMinTempEnable'] != 'disabled' ? $data['iemConfigProbeMinTempThreshold'] : null);
        $low_warn_limit  = ($data['iemConfigProbeLowTempEnable'] != 'disabled' ? $data['iemConfigProbeLowTempThreshold'] : null);
        $high_warn_limit = ($data['iemConfigProbeHighTempEnable'] != 'disabled' ? $data['iemConfigProbeHighTempThreshold'] : null);
        $high_limit      = ($data['iemConfigProbeMaxTempEnable'] != 'disabled' ? $data['iemConfigProbeMaxTempThreshold'] : null);

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
    }

    $apc_env_data = snmpwalk_cache_oid($device, 'emsProbeStatus', array(), 'PowerNet-MIB');

    foreach ($apc_env_data as $index => $data) {
        if ($data['emsProbeStatusProbeCommStatus'] != 'commsNeverDiscovered') {
            $descr = $data['emsProbeStatusProbeName'];
            $current = $data['emsProbeStatusProbeTemperature'];
            $sensorType = 'apc';
            $oid = '.1.3.6.1.4.1.318.1.1.10.3.13.1.1.3.' . $index;
            $low_limit = $data['emsProbeStatusProbeMinTempThresh'];
            $low_warn_limit = $data['emsProbeStatusProbeLowTempThresh'];
            $high_warn_limit = $data['emsProbeStatusProbeHighTempThresh'];
            $high_limit = $data['emsProbeStatusProbeMaxTempThresh'];

            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
        }
    }

    // InRow Chiller.
    // A silly check to find out if it's the right hardware.
    $oids = snmp_get($device, 'airIRRCGroupSetpointsCoolMetric.0', '-OsqnU', 'PowerNet-MIB');
    if ($oids) {
        d_echo('InRow Chiller ');
        $temps = array();
        $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.7']            = 'Rack Inlet'; //airIRRCUnitStatusRackInletTempMetric
        $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.9']            = 'Supply Air'; //airIRRCUnitStatusSupplyAirTempMetric
        $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.11']            = 'Return Air'; //airIRRCUnitStatusReturnAirTempMetric
        $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.24'] = 'Entering Fluid'; //airIRRCUnitStatusEnteringFluidTemperatureMetric
        $temps['.1.3.6.1.4.1.318.1.1.13.3.2.2.2.26']  = 'Leaving Fluid'; //airIRRCUnitStatusLeavingFluidTemperatureMetric
        foreach ($temps as $obj => $descr) {
            $oids                   = snmp_get($device, $obj.'.0', '-OsqnU', 'PowerNet-MIB');
            list($oid,$current) = explode(' ', $oids);
            $divisor            = 10;
            $sensorType         = substr($descr, 0, 2);
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', $sensorType, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }

    // Portable Air Conditioner
    $set_oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.13.2.2.4.0', '-OsqnU', '');
    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.13.2.2.10.0', '-OsqnU', '');

    if ($oids !== false) {
        d_echo('Portable Supply ');
        list($oid,$current_raw) = explode(' ', $oids);
        $precision              = 10;
        $current                = ($current_raw / $precision);
        $sensorType             = 'apc';
        $index                  = 0;
        if ($set_oids !== false) {
            list(, $set_point_raw) = explode(' ', $set_oids);
            $set_point             = ($set_point_raw / $precision);
            $descr                 = 'Supply Temp - Setpoint: '.$set_point.'&deg;C';
        } else {
            $descr = 'Supply Temperature';
        }

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
    }

    unset($oids);
    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.13.2.2.12.0', '-OsqnU', '');

    if ($oids !== false) {
        d_echo('Portable Return ');
        list($oid,$current_raw) = explode(' ', $oids);
        $precision              = 10;
        $current                = ($current_raw / $precision);
        $sensorType             = 'apc';
        $index                  = 1;
        if ($set_oids !== false) {
            list(, $set_point_raw) = explode(' ', $set_oids);
            $set_point             = ($set_point_raw / $precision);
            $descr                 = 'Return Temp - Setpoint: '.$set_point.'&deg;C';
        } else {
            $descr = 'Return Temperature';
        }

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
    }

    unset($oids);
    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.13.2.2.14.0', '-OsqnU', '');

    if ($oids !== false) {
        d_echo('Portable Remote ');
        list($oid,$current_raw) = explode(' ', $oids);
        $precision              = 10;
        $current                = ($current_raw / $precision);
        $sensorType             = 'apc';
        $index                  = 2;
        if ($set_oids !== false) {
            list(, $set_point_raw) = explode(' ', $set_oids);
            $set_point             = ($set_point_raw / $precision);
            $descr                 = 'Remote Temp - Setpoint: '.$set_point.'&deg;C';
        } else {
            $descr = 'Remote Temperature';
        }

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, $precision, '1', null, null, null, null, $current);
    }
}//end if
