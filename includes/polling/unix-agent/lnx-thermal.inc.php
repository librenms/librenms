<?php

require_once 'includes/discovery/functions.inc.php';

$thermal_data = $agent_data['lnx_thermal'];
if (isset($thermal_data) && $thermal_data != '') {
    $thermal_zones = explode("\n", $thermal_data);
    $index = 0;

    foreach ($thermal_zones as $thermal_zone) {
        $index++;
        [ $name, $enabled, $type, $temperature ] = explode(" ", $thermal_zone);
        $temperature /= 1000;

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

        dbUpdate(
            ['sensor_current' => $temperature],
            'sensors',
            '`sensor_index` = ? AND `sensor_class` = ? AND `poller_type` = ? AND `device_id` = ?',
            [$index, 'temperature', 'agent', $device['device_id']]);
    }
}
