<?php

$cur_oid = '.1.3.6.1.3.94.1.8.1.6.';

// These sensors are not provided as tables. They are strings of the form:
//    Sensor Name: Value
//
// The list is also a mix of voltages, temperatures, and state, and uses both F and C for temperatures
// The order is not stable between software versions

if (is_array($pre_cache['dell-powervault'])) {
    foreach ($pre_cache['dell-powervault'] as $index => $entry) {
        if (str_contains($entry['connUnitSensorMessage'], 'Voltage')) {
            $connUnitSensorMessage = explode(':', $entry['connUnitSensorMessage']);
            preg_match('/^ ([0-9]+\.[0-9]+)V/$', array_pop($connUnitSensorMessage), $matches)[1];
            $value = $matches[1];
            $descr = implode(':', $connUnitSensorMessage);

            discover_sensor($valid['sensor'], 'voltage', $device, $cur_oid . $index, $index, 'dellme', $descr, '1', '1', null, null, null, null, $value);
        }
    }
}
unset($cur_oid,
    $connUnitSensorMessage,
    $value,
    $descr
);
