<?php

require_once 'includes/discovery/functions.inc.php';

$thermal_data = $agent_data['lnx_thermal'];
if (isset($thermal_data) && $thermal_data != '') {
    $thermal_zones = explode("\n", $thermal_data);
    $index = 0;

    foreach ($thermal_zones as $thermal_zone) {
        $index++;
        [ $name, $enabled, $type, $temperature ] = explode(" ", $thermal_zone);

        discover_sensor(
            $valid['sensor'],
            'temperature',
            $device,
            '',
            $index,
            'temp',
            "$type: $type",
            '1',
            '1',
            null,
            null,
            null,
            null,
            $temperature,
            'agent');
    }
}
