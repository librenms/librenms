<?php

use LibreNMS\Util\Number;

$tempTable = SnmpQuery::walk('FS-SWITCH-V2-MIB::transTemperinformationTable')->table(1);
$ifIndexToName = SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck();

foreach ($tempTable as $ifIndex => $current) {
    $ifName = $ifIndexToName[$ifIndex] ?? $ifIndex;

    if (! empty($current['FS-SWITCH-V2-MIB::temperCurrent']) && $current['FS-SWITCH-V2-MIB::temperCurrent'] !== '0.00') {
        foreach (explode(',', $current['FS-SWITCH-V2-MIB::temperCurrent']) as $channel => $value) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'temperature',
                'sensor_oid' => ".1.3.6.1.4.1.52642.1.37.1.10.2.1.5.$ifIndex",
                'sensor_index' => "$ifIndex.$channel",
                'sensor_type' => 'transceiver',
                'sensor_descr' => "$ifName xcvr temperature",
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => $current['FS-SWITCH-V2-MIB::temperLowAlarmThreshold'] ?? null,
                'sensor_limit_low_warn' => $current['FS-SWITCH-V2-MIB::temperLowWarnThreshold'] ?? null,
                'sensor_limit_warn' => $current['FS-SWITCH-V2-MIB::temperHighWarnThreshold'] ?? null,
                'sensor_limit' => $current['FS-SWITCH-V2-MIB::temperHighAlarmThreshold'] ?? null,
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

unset($tempTable, $current);
