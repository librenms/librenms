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
                if (isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrent']) && $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrent'] != '-100001') {
                    $channelDescr = count($module_data) > 2 ? " Channel $cmmTransChannelIndex" : '';

                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'current',
                        'sensor_oid' => ".1.3.6.1.4.1.36673.100.1.2.3.1.12.$cmmStackUnitIndex.$cmmTransIndex.$cmmTransChannelIndex",
                        'sensor_index' => "$cmmStackUnitIndex.$cmmTransIndex.$cmmTransChannelIndex",
                        'sensor_type' => 'ocnos',
                        'sensor_descr' => "$ifName$channelDescr xcvr bias",
                        'sensor_divisor' => $divisor,
                        'sensor_multiplier' => 1,
                        'sensor_limit' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrCriticalThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrCriticalThresholdMax'] / $divisor : null,
                        'sensor_limit_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrAlertThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrAlertThresholdMax'] / $divisor : null,
                        'sensor_limit_low' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrCriticalThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrCriticalThresholdMin'] / $divisor : null,
                        'sensor_limit_low_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrAlertThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrAlertThresholdMin'] / $divisor : null,
                        'sensor_current' => $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransLaserBiasCurrent'] / $divisor,
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
