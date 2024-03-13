<?php

require_once 'includes/discovery/functions.inc.php';

$thermal_data = $agent_data['lnx_thermal'];
if (isset($thermal_data) && $thermal_data != '') {
    $thermal_zones = explode("\n", $thermal_data);
    $index = 0;

    foreach ($thermal_zones as $thermal_zone) {
        $index++;
        [ $name, $enabled, $type, $temperature ] = explode(' ', $thermal_zone);
        $temperature /= 1000;

        // Add the sensor to database (does not update value if already exists).
        discover_sensor(
            $valid['sensor'],
            'temperature',
            $device,
            '',
            $index,
            'temp',
            "$name ($type)",
            '1',
            '1',
            null,
            null,
            null,
            null,
            $temperature,
            'agent');

        // Update the sensor value.
        dbUpdate(
            ['sensor_current' => $temperature],
            'sensors',
            '`sensor_index` = ? AND `sensor_class` = ? AND `poller_type` = ? AND `device_id` = ?',
            [$index, 'temperature', 'agent', $device['device_id']]);

        // Add to the global list of sensor to generate RRD.
        $tmp_agent_sensors = dbFetchRow(
            "SELECT * FROM `sensors` WHERE `sensor_index` = ? AND `device_id` = ? AND `sensor_class` = 'temperature' AND `poller_type` = 'agent' AND `sensor_deleted` = 0 LIMIT 1",
            [$index, $device['device_id']]);
        $tmp_agent_sensors['new_value'] = $temperature;
        $agent_sensors[] = $tmp_agent_sensors;
        unset($tmp_agent_sensors);
    }
}
