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
                if (isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltage']) && $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltage'] != '-100001') {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'voltage',
                        'sensor_oid' => ".1.3.6.1.4.1.36673.100.1.2.3.1.7.$cmmStackUnitIndex.$cmmTransIndex.$cmmTransChannelIndex",
                        'sensor_index' => "$cmmStackUnitIndex.$cmmTransIndex",
                        'sensor_type' => 'ocnos',
                        'sensor_descr' => "$ifName xcvr voltage",
                        'sensor_divisor' => $divisor,
                        'sensor_multiplier' => 1,
                        'sensor_limit' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltCriticalThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltCriticalThresholdMax'] / $divisor : null,
                        'sensor_limit_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltAlertThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltAlertThresholdMax'] / $divisor : null,
                        'sensor_limit_low' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltCriticalThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltCriticalThresholdMin'] / $divisor : null,
                        'sensor_limit_low_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltAlertThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltAlertThresholdMin'] / $divisor : null,
                        'sensor_current' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltage']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransVoltage'] / $divisor : null,
                        'entPhysicalIndex' => $cmmStackUnitIndex * 10000 + $cmmTransIndex,
                        'entPhysicalIndex_measured' => 'port',
                        'user_func' => null,
                        'group' => 'transceiver',
                    ]));

                    continue 2; // common across channels
                }
            }
        }
    }
}
