<?php

echo ' TP-Link Sensors';
$tplink_ddm_states = [
    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'False'],
    ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'True'],
];
$tplink_ddm_sensors = [
    'ddmStatusTemperature' => [
        'class' => 'temperature',
        'descr' => 'DDM Temperature',
        'oid' => '.1.3.6.1.4.1.11863.6.96.1.7.1.1.2',
        'limits' => [
            'mib' => 'TPLINK-DDMTEMPTHRESHOLD-MIB',
            'oid' => 'ddmTempThresholdEntry',
            'high_limit' => 'ddmTempThresholdHighAlarm',
            'low_limit' => 'ddmTempThresholdLowAlarm',
            'low_warn_limit' => 'ddmTempThresholdLowWarn',
            'warn_limit' => 'ddmTempThresholdHighWarn',
        ],
    ],
    'ddmStatusVoltage' => [
        'class' => 'voltage',
        'descr' => 'DDM Voltage',
        'oid' => '.1.3.6.1.4.1.11863.6.96.1.7.1.1.3',
        'limits' => [
            'mib' => 'TPLINK-DDMVOLTHRESHOLD-MIB',
            'oid' => 'ddmVolThresholdEntry',
            'high_limit' => 'ddmVolThresholdHighAlarm',
            'low_limit' => 'ddmVolThresholdLowAlarm',
            'low_warn_limit' => 'ddmVolThresholdLowWarn',
            'warn_limit' => 'ddmVolThresholdHighWarn',
        ],
    ],
    'ddmStatusBiasCurrent' => [
        'class' => 'current',
        'descr' => 'DDM Bias Current',
        'divisor' => 1000,
        'oid' => '.1.3.6.1.4.1.11863.6.96.1.7.1.1.4',
        'limits' => [
            'mib' => 'TPLINK-DDMBIASCURTHRESHOLD-MIB',
            'oid' => 'ddmBiasCurThresholdEntry',
            'high_limit' => 'ddmBiasCurThresholdHighAlarm',
            'low_limit' => 'ddmBiasCurThresholdLowAlarm',
            'low_warn_limit' => 'ddmBiasCurThresholdLowWarn',
            'warn_limit' => 'ddmBiasCurThresholdHighWarn',
        ],
    ],
    'ddmStatusTxPow' => [
        'class' => 'dbm',
        'descr' => 'DDM TX Power',
        'oid' => '.1.3.6.1.4.1.11863.6.96.1.7.1.1.5',
        'limits' => [
            'mib' => 'TPLINK-DDMTXPOWTHRESHOLD-MIB',
            'oid' => 'ddmTxPowThresholdEntry',
            'high_limit' => 'ddmTxPowThresholdHighAlarm',
            'low_limit' => 'ddmTxPowThresholdLowAlarm',
            'low_warn_limit' => 'ddmTxPowThresholdLowWarn',
            'warn_limit' => 'ddmTxPowThresholdHighWarn',
        ],
        'user_func' => 'mw_to_dbm',
    ],
    'ddmStatusRxPow' => [
        'class' => 'dbm',
        'descr' => 'DDM RX Power',
        'oid' => '.1.3.6.1.4.1.11863.6.96.1.7.1.1.6',
        'limits' => [
            'mib' => 'TPLINK-DDMRXPOWTHRESHOLD-MIB',
            'oid' => 'ddmRxPowThresholdEntry',
            'high_limit' => 'ddmRxPowThresholdHighAlarm',
            'low_limit' => 'ddmRxPowThresholdLowAlarm',
            'low_warn_limit' => 'ddmRxPowThresholdLowWarn',
            'warn_limit' => 'ddmRxPowThresholdHighWarn',
        ],
        'user_func' => 'mw_to_dbm',
    ],
    'ddmStatusDataReady' => [
        'class' => 'state',
        'descr' => 'DDM Data Ready',
        'oid' => '.1.3.6.1.4.1.11863.6.96.1.7.1.1.7',
    ],
    'ddmStatusLossSignal' => [
        'class' => 'state',
        'descr' => 'DDM Loss of Signal',
        'oid' => '.1.3.6.1.4.1.11863.6.96.1.7.1.1.8',
    ],
    'ddmStatusTxFault' => [
        'class' => 'state',
        'descr' => 'DDM TX Fault',
        'oid' => '.1.3.6.1.4.1.11863.6.96.1.7.1.1.9',
    ],
];

if (! empty($pre_cache['ddmStatusEntry'])){
    foreach($tplink_ddm_sensors as $sensor_name => $sensor_info) {
        /* Query for sensor limits. */
        if(isset($sensor_info['limits'])) {
            $sensor_limits = snmpwalk_cache_multi_oid(
                $device,
                $sensor_info['limits']['oid'],
                [],
                $sensor_info['limits']['mib'],
            );
        } else {
            $sensor_limits = [];
        }

        /* Go through each DDM status entry and create sensors only for
           valid ports (e.g., has SFP module plugged in). */
        foreach ($pre_cache['ddmStatusEntry'] as $index => $ddm_status) {
            $sensor_value = $ddm_status[$sensor_name];
            if ($sensor_value != 'N/A') {
                if ($sensor_info['class'] == 'state') {
                    /* Work around TP-Link's string 'True'/'False' booleans. */
                    if ($sensor_value == 'True'){
                        $sensor_value = 1;
                    } elseif ($sensor_value == 'False') {
                        $sensor_value = 0;
                    } else {
                        $sensor_value = null;
                    }
                    create_state_index($sensor_name, $tplink_ddm_states);
                }

                $sensor_divisor = 1;
                if (isset($sensor_info['divisor'])) {
                    $sensor_divisor = $sensor_info['divisor'];
                }

                $sensor_multiplier = 1;
                if (isset($sensor_info['multiplier'])) {
                    $sensor_multiplier = $sensor_info['multiplier'];
                }

                $sensor_user_func = null;
                if (isset($sensor_info['user_func'])) {
                    $sensor_user_func = $sensor_info['user_func'];
                }

                $low_limit = null;
                $low_warn_limit = null;
                $high_limit = null;
                $warn_limit = null;
                $limits = ['low_limit', 'low_warn_limit', 'warn_limit', 'high_limit'];
                foreach ($limits as $limit) {
                    $$limit = $sensor_limits[$index][$sensor_info['limits'][$limit]];
                    if (is_numeric($$limit)) {
                        $$limit = ($$limit / $sensor_divisor) * $sensor_multiplier;
                    }
                    if (is_numeric($$limit) && isset($sensor_user_func) && is_callable($sensor_user_func)) {
                        $$limit = $sensor_user_func($$limit);
                    }
                }

                $sensor_oid = $sensor_info['oid'] . '.' . $index;
                discover_sensor(
                    $valid['sensor'],
                    $sensor_info['class'],
                    $device,
                    $sensor_oid,
                    $sensor_name . '.' . $index,
                    $sensor_name,
                    $sensor_info['descr'] . ' ' . $ddm_status['ddmStatusPort'],
                    $sensor_divisor,
                    $sensor_multiplier,
                    $low_limit,
                    $low_warn_limit,
                    $warn_limit,
                    $high_limit,
                    $sensor_value,
                    'snmp',
                    $index,
                    'ports',
                    $sensor_user_func,
                    'SFPs'
                );
                if ($sensor_info['class'] == 'state') {
                    create_sensor_to_state_index($device, $sensor_name, $sensor_oid);
                }
            }
        }
    }
}

unset($tplink_ddm_states, $tplink_ddm_sensors);
