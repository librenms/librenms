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
        if (str_contains($entry['connUnitSensorMessage'], 'Status')) {
            $states = [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Not OK'],
            ];

            $connUnitSensorMessage = explode(':', $entry['connUnitSensorMessage']);
            $value = array_pop($connUnitSensorMessage) === ' OK' ? 1 : 2;
            $descr = implode(':', $connUnitSensorMessage);

            create_state_index($descr, $states);

            discover_sensor(null, 'state', $device, $cur_oid . $index, $index, 'dellme', $descr, '1', '1', null, null, null, null, $value);
        }
    }
}
unset($cur_oid,
    $connUnitSensorMessage,
    $value,
    $descr,
    $states,
    $oids
);
