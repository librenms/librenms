<?php

if (empty($os)) {
    $os = OS::make($device);
}

if ($os instanceof \LibreNMS\OS\Ocnos) {
    $metric_data = \SnmpQuery::cache()->enumStrings()->walk(['IPI-CMM-CHASSIS-MIB::cmmTransDDMTable', 'IPI-CMM-CHASSIS-MIB::cmmTransType'])->table(3);
    $divisor = 100;

    foreach ($metric_data as $cmmStackUnitIndex => $chassis_data) {
        foreach ($chassis_data as $cmmTransIndex => $module_data) {
            $ifName = $os->guessIfName($cmmTransIndex, $module_data['IPI-CMM-CHASSIS-MIB::cmmTransType'] ?? 'unknown');

            foreach ($module_data as $cmmTransChannelIndex => $channel_data) {
                if (isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTemperature']) && $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTemperature'] != '-100001') {
                    app('sensor-discovery')->discover(new \App\Models\Sensor([
                        'poller_type' => 'snmp',
                        'sensor_class' => 'temperature',
                        'sensor_oid' => ".1.3.6.1.4.1.36673.100.1.2.3.1.2.$cmmStackUnitIndex.$cmmTransIndex.$cmmTransChannelIndex",
                        'sensor_index' => "$cmmStackUnitIndex.$cmmTransIndex",
                        'sensor_type' => 'transceiver',
                        'sensor_descr' => "$ifName xcvr temperature",
                        'sensor_divisor' => $divisor,
                        'sensor_multiplier' => 1,
                        'sensor_limit' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTempCriticalThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTempCriticalThresholdMax'] / $divisor : null,
                        'sensor_limit_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTempAlertThresholdMax']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTempAlertThresholdMax'] / $divisor : null,
                        'sensor_limit_low' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTempCriticalThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTempCriticalThresholdMin'] / $divisor : null,
                        'sensor_limit_low_warn' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTempAlertThresholdMin']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTempAlertThresholdMin'] / $divisor : null,
                        'sensor_current' => isset($channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTemperature']) ? $channel_data['IPI-CMM-CHASSIS-MIB::cmmTransTemperature'] / $divisor : null,
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
