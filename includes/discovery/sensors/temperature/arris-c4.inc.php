<?php

/**
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$oids = SnmpQuery::walk([
    'CADANT-CMTS-EQUIPMENT-MIB::cardTemperature',
    'CADANT-CMTS-EQUIPMENT-MIB::cardName',
    'CADANT-CMTS-EQUIPMENT-MIB::cardTemperatureHighWarn',
    'CADANT-CMTS-EQUIPMENT-MIB::cardTemperatureHighError',
])->table(2);

foreach ($oids as $unit => $tmp) {
    foreach ($tmp as $index => $entry) {
        $value = $entry['CADANT-CMTS-EQUIPMENT-MIB::cardTemperature'];
        if ($value !== '999') {
            $oid = '.1.3.6.1.4.1.4998.1.1.10.1.4.2.1.29.' . $unit . '.' . $index;
            $descr = $entry['CADANT-CMTS-EQUIPMENT-MIB::cardName'];
            $warnlimit = $entry['CADANT-CMTS-EQUIPMENT-MIB::cardTemperatureHighWarn'];
            $limit = $entry['CADANT-CMTS-EQUIPMENT-MIB::cardTemperatureHighError'];

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'temperature',
                'sensor_oid' => $oid,
                'sensor_index' => $unit . '.' . $index,
                'sensor_type' => 'cmts',
                'sensor_descr' => $descr,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_limit_warn' => $warnlimit,
                'sensor_limit' => $limit,
                'sensor_current' => $value,
                'entPhysicalIndex' => null,
                'entPhysicalIndex_measured' => null,
                'user_func' => null,
                'group' => null,
            ]));
        }
    }
}
