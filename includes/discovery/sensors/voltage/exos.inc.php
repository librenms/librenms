<?php

echo 'Exos ';

if (is_array($pre_cache['exos']['connUnitSensorTable'])) {
    foreach ($pre_cache['exos']['connUnitSensorTable'] as $index => $entry) {
        if (preg_match('/Voltage.* ([: 0-9\.]+V)/', $entry['connUnitSensorMessage'], $temp_value)) {
            $value = str_replace('V', '', $temp_value[1]);
            discover_sensor($valid['sensor'], 'voltage', $device, ".1.3.6.1.3.94.1.8.1.6.{$index}", $entry['connUnitSensorIndex'], 'exos', $entry['connUnitSensorName'], 1, '1', null, null, null, null, $value);
        }
    }
}
