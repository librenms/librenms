<?php

/**
 * Multi-sensor graph authentication
 *
 * Allows graphing multiple sensors (of the same type) from different devices
 * on a single graph. Sensors are specified as comma-separated IDs.
 *
 * Example URL:
 * graph.php?type=multisensor_graph&id=123,456,789
 */

use App\Models\Sensor;

$multisensor_sensors = [];

if (! $auth) {
    foreach (explode(',', (string) $vars['id']) as $sensor_id) {
        $sensor_id = trim($sensor_id);
        if (! is_numeric($sensor_id)) {
            $auth = false;
            break;
        }

        $sensor = Sensor::find($sensor_id);
        if (! $sensor) {
            $auth = false;
            break;
        }

        // Check if user has permission to view this sensor's device
        if (! device_permitted($sensor->device_id)) {
            $auth = false;
            break;
        }

        $multisensor_sensors[] = $sensor;
        $auth = true;
    }
}

$title = 'Multi Sensor :: ';
