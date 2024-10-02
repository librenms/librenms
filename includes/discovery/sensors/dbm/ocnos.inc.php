<?php

if (empty($os)) {
    $os = OS::make($device);
}

if ($os instanceof \LibreNMS\OS\Ocnos) {
    $metric_data = \SnmpQuery::cache()->enumStrings()->walk(['IPI-CMM-CHASSIS-MIB::cmmTransDDMTable', 'IPI-CMM-CHASSIS-MIB::cmmTransType'])->table(3);
    $divisor = 1000;

    foreach ($metric_data as $cmmStackUnitIndex => $chassis_data) {
        foreach ($chassis_data as $cmmTransIndex => $module_data) {
            $ifName = $os->guessIfName($cmmTransIndex, $module_data['IPI-CMM-CHASSIS-MIB::cmmTransType'] ?? 'unknown');

            foreach ($module_data as $cmmTransChannelIndex => $channel_data) {
                $channelDescr = count($module_data) > 2 ? " Channel $cmmTransChannelIndex" : '';

                // power-tx
                if (isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerSupported']) && $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerSupported'] == 'supported') {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'dbm',
                        'sensor_oid' => ".1.3.6.1.4.1.36673.100.1.2.3.1.17.$cmmStackUnitIndex.$cmmTransIndex.$cmmTransChannelIndex",
                        'sensor_index' => "tx-$cmmStackUnitIndex.$cmmTransIndex.$cmmTransChannelIndex",
                        'sensor_type' => 'ocnos',
                        'sensor_descr' => "$ifName$channelDescr xcvr TX power",
                        'sensor_divisor' => $divisor,
                        'sensor_multiplier' => 1,
                        'sensor_limit' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerCriticalThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerCriticalThresholdMax'] / $divisor : null,
                        'sensor_limit_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerAlertThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerAlertThresholdMax'] / $divisor : null,
                        'sensor_limit_low' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerCriticalThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerCriticalThresholdMin'] / $divisor : null,
                        'sensor_limit_low_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerAlertThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPowerAlertThresholdMin'] / $divisor : null,
                        'sensor_current' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPower']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTxPower'] / $divisor : null,
                        'entPhysicalIndex' => $cmmStackUnitIndex * 10000 + $cmmTransIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'user_func' => null,
                        'group' => 'transceiver',
                    ]));
                }

                // power-rx
                if (isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerSupported']) && $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerSupported'] == 'supported') {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'dbm',
                        'sensor_oid' => ".1.3.6.1.4.1.36673.100.1.2.3.1.22.$cmmStackUnitIndex.$cmmTransIndex.$cmmTransChannelIndex",
                        'sensor_index' => "rx-$cmmStackUnitIndex.$cmmTransIndex.$cmmTransChannelIndex",
                        'sensor_type' => 'ocnos',
                        'sensor_descr' => "$ifName$channelDescr xcvr RX power",
                        'sensor_divisor' => $divisor,
                        'sensor_multiplier' => 1,
                        'sensor_limit' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerCriticalThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerCriticalThresholdMax'] / $divisor : null,
                        'sensor_limit_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerAlertThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerAlertThresholdMax'] / $divisor : null,
                        'sensor_limit_low' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerCriticalThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerCriticalThresholdMin'] / $divisor : null,
                        'sensor_limit_low_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerAlertThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPowerAlertThresholdMin'] / $divisor : null,
                        'sensor_current' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPower']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransRxPower'] / $divisor : null,
                        'entPhysicalIndex' => $cmmStackUnitIndex * 10000 + $cmmTransIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'user_func' => null,
                        'group' => 'transceiver',
                    ]));
                }
            }
        }
    }
}
