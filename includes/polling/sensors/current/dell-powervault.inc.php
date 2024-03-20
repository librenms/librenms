<?php

if ($sensor['sensor_type'] === 'dellme') {
    $connUnitSensorMessage = explode(':', $sensor_value);
    preg_match('/^ ([0-9]+\.[0-9]+)A$/', array_pop($connUnitSensorMessage), $matches);
    $sensor_value = $matches[1];

    unset($matches,
        $connUnitSensorMessage
    );
}
