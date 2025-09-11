<?php

use LibreNMS\Util\Number;

$powerTables = SnmpQuery::walk('FS-SWITCH-V2-MIB::transReceivePowerTable')->table(1);

if (! empty($powerTables)) {
    SnmpQuery::walk('FS-SWITCH-V2-MIB::transTransmitPowerTable')->table(1, $powerTables);
}

$ifIndexToName = SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck();

foreach ($powerTables as $ifIndex => $current) {
    $ifName = $ifIndexToName[$ifIndex] ?? $ifIndex;

    // power-rx
    if (! empty($current['FS-SWITCH-V2-MIB::receivepowerCurrent']) && $current['FS-SWITCH-V2-MIB::receivepowerCurrent'] !== '0.00') {
        foreach (explode(',', $current['FS-SWITCH-V2-MIB::receivepowerCurrent']) as $channel => $value) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'dbm',
                'sensor_oid' => ".1.3.6.1.4.1.52642.1.37.1.10.6.1.5.$ifIndex",
                'sensor_index' => "rx-$ifIndex.$channel",
                'sensor_type' => 'fs-centec',
                'sensor_descr' => "$ifName:$channel xcvr TX power",
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => $current['FS-SWITCH-V2-MIB::receivepowerLowAlarmThreshold'] ?? null,
                'sensor_limit_low_warn' => $current['FS-SWITCH-V2-MIB::receivepowerLowWarnThreshold'] ?? null,
                'sensor_limit_warn' => $current['FS-SWITCH-V2-MIB::receivepowerHighWarnThreshold'] ?? null,
                'sensor_limit' => $current['FS-SWITCH-V2-MIB::receivepowerHighAlarmThreshold'] ?? null,
                'sensor_current' => Number::cast($value),
                'entPhysicalIndex' => $ifIndex,
                'entPhysicalIndex_measured' => 'port',
                'user_func' => 'fsParseChannelValue',
                'group' => 'transceiver',
            ]));
        }
    }

    // power-tx
    if (! empty($current['FS-SWITCH-V2-MIB::transpowerCurrent']) && $current['FS-SWITCH-V2-MIB::transpowerCurrent'] !== '0.00') {
        foreach (explode(',', $current['FS-SWITCH-V2-MIB::transpowerCurrent']) as $channel => $value) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'dbm',
                'sensor_oid' => ".1.3.6.1.4.1.52642.1.37.1.10.5.1.5.$ifIndex",
                'sensor_index' => "tx-$ifIndex.$channel",
                'sensor_type' => 'fs-centec',
                'sensor_descr' => "$ifName:$channel xcvr RX power",
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => $current['FS-SWITCH-V2-MIB::transpowerLowAlarmThreshold'] ?? null,
                'sensor_limit_low_warn' => $current['FS-SWITCH-V2-MIB::transpowerLowWarnThreshold'] ?? null,
                'sensor_limit_warn' => $current['FS-SWITCH-V2-MIB::transpowerHighWarnThreshold'] ?? null,
                'sensor_limit' => $current['FS-SWITCH-V2-MIB::transpowerHighAlarmThreshold'] ?? null,
                'sensor_current' => Number::cast($value),
                'entPhysicalIndex' => $ifIndex,
                'entPhysicalIndex_measured' => 'port',
                'user_func' => 'fsParseChannelValue',
                'group' => 'transceiver',
            ]));
        }
    }
}

unset($powerTables, $current);
