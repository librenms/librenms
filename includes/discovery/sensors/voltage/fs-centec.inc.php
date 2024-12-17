<?php

use LibreNMS\Util\Number;

$voltageTable = SnmpQuery::walk('FS-SWITCH-V2-MIB::transVoltageinformationTable')->table(1);
$ifIndexToName = SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck();

foreach ($voltageTable as $ifIndex => $current) {
    $ifName = $ifIndexToName[$ifIndex] ?? $ifIndex;

    if (! empty($current['FS-SWITCH-V2-MIB::voltageCurrent']) && $current['FS-SWITCH-V2-MIB::voltageCurrent'] !== '0.00') {
        foreach (explode(',', $current['FS-SWITCH-V2-MIB::voltageCurrent']) as $channel => $value) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'voltage',
                'sensor_oid' => ".1.3.6.1.4.1.52642.1.37.1.10.3.1.5.$ifIndex",
                'sensor_index' => "$ifIndex.$channel",
                'sensor_type' => 'fs-centec',
                'sensor_descr' => "$ifName xcvr voltage",
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => $current['FS-SWITCH-V2-MIB::voltageLowAlarmThreshold'] ?? null,
                'sensor_limit_low_warn' => $current['FS-SWITCH-V2-MIB::voltageLowWarnThreshold'] ?? null,
                'sensor_limit_warn' => $current['FS-SWITCH-V2-MIB::voltageHighWarnThreshold'] ?? null,
                'sensor_limit' => $current['FS-SWITCH-V2-MIB::voltageHighAlarmThreshold'] ?? null,
                'sensor_current' => Number::cast($value),
                'entPhysicalIndex' => $ifIndex,
                'entPhysicalIndex_measured' => 'port',
                'user_func' => 'fsParseChannelValue',
                'group' => 'transceiver',
            ]));

            break; // only discover one sensor
        }
    }
}

unset($voltageTable, $current);
