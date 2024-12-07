<?php

use LibreNMS\Util\Number;

$biasTable = SnmpQuery::walk('FS-SWITCH-V2-MIB::transBiasinformationTable')->table(1);

$ifIndexToName = SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck();

foreach ($biasTable as $ifIndex => $current) {
    $ifName = $ifIndexToName[$ifIndex] ?? $ifIndex;
    if (! empty($current['FS-SWITCH-V2-MIB::biasCurrent']) && $current['FS-SWITCH-V2-MIB::biasCurrent'] !== '0.00') {
        foreach (explode(',', $current['FS-SWITCH-V2-MIB::biasCurrent']) as $channel => $value) {
            $divisor = 1000;

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'current',
                'sensor_oid' => ".1.3.6.1.4.1.52642.1.37.1.10.4.1.5.$ifIndex",
                'sensor_index' => "$ifIndex.$channel",
                'sensor_type' => 'fs-centec',
                'sensor_descr' => "$ifName:$channel xcvr bias",
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => $current['FS-SWITCH-V2-MIB::biasLowAlarmThreshold'] ?? null,
                'sensor_limit_low_warn' => $current['FS-SWITCH-V2-MIB::biasLowWarnThreshold'] ?? null,
                'sensor_limit_warn' => $current['FS-SWITCH-V2-MIB::biasHighWarnThreshold'] ?? null,
                'sensor_limit' => $current['FS-SWITCH-V2-MIB::biasHighAlarmThreshold'] ?? null,
                'sensor_current' => Number::cast($value) / $divisor,
                'entPhysicalIndex' => $ifIndex,
                'entPhysicalIndex_measured' => 'port',
                'user_func' => 'fsParseChannelValue',
                'group' => 'transceiver',
            ]));
        }
    }
}

unset($biasTable, $current);
