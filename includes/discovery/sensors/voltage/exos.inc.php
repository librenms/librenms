<?php

/**
 * exos.inc.php
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$oids = SnmpQuery::cache()->hideMib()->numericIndex()->walk('FCMGMT-MIB::connUnitSensorTable')->valuesByIndex();

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        if (preg_match('/Voltage.* ([: 0-9\.]+V)/', $entry['connUnitSensorMessage'], $temp_value)) {
            $value = str_replace('V', '', $temp_value[1]);
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'voltage',
                'sensor_oid' => '.1.3.6.1.3.94.1.8.1.6.' . $index,
                'sensor_index' => $entry['connUnitSensorIndex'],
                'sensor_type' => 'exos',
                'sensor_descr' => $entry['connUnitSensorName'],
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_limit_warn' => null,
                'sensor_limit' => null,
                'sensor_current' => $value,
                'entPhysicalIndex' => null,
                'entPhysicalIndex_measured' => null,
                'user_func' => null,
                'group' => null,
            ]));
        }
    }
}
