<?php

if ($sensor['sensor_type'] === 'dellme') {
    $connUnitSensorMessage = explode(':', $sensor_value);
    $sensor_value = array_pop($connUnitSensorMessage) === ' OK' ? 1 : 2;
    unset($connUnitSensorMessage);
}
