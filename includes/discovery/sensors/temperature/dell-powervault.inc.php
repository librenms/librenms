<?php

$cur_oid = '.1.3.6.1.3.94.1.8.1.6.';

// These sensors are not provided as tables. They are strings of the form:
//    Sensor Name: Value
//
// The list is also a mix of voltages, temperatures, and state, and uses both F and C for temperatures
// The order is not stable between software versions

$oids = SnmpQuery::cache()->hideMib()->numericIndex()->walk('FCMGMT-MIB::connUnitSensorMessage')->valuesByIndex();

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        if (str_contains($entry['connUnitSensorMessage'], 'Temp')) {
            $connUnitSensorMessage = explode(':', $entry['connUnitSensorMessage']);
            preg_match('/^ ([0-9]+) C ([0-9]+\.[0-9]+)F$/', array_pop($connUnitSensorMessage), $matches);
            $value = $matches[1];
            $descr = implode(':', $connUnitSensorMessage);
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'temperature',
                'sensor_oid' => $cur_oid . $index,
                'sensor_index' => $index,
                'sensor_type' => 'dellme',
                'sensor_descr' => $descr,
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
unset($cur_oid,
    $connUnitSensorMessage,
    $value,
    $descr,
    $oids
);
