<?php

use App\Models\Sensor;
use LibreNMS\Util\Number;

$sensors = Sensor::where('device_id', $device['device_id'])
    ->where('entPhysicalIndex', $port['ifIndex'])
    ->where('entPhysicalIndex_measured', 'ports')
    ->orderBy('sensor_type', 'ASC');

foreach ($sensors as $sensor) {
    if ($sensor->poller_type == 'ipmi') {
        $sensor_descr = ipmiSensorName($device['hardware'], $sensor->sensor_descr);
    } else {
        $sensor_descr = $sensor->sensor_descr;
    }

    $sensor_current = $graph_type == 'sensor_state' ? get_state_label($sensor) : get_sensor_label_color($sensor);

    echo "<div class='panel panel-default'>\n" .
         "    <div class='panel-heading'>\n" .
         "        <h3 class='panel-title'>$sensor_descr <div class='pull-right'>$sensor_current";

    if ($sensor->sensor_limit_low) {
        $sensor_limit_low = trim(Number::formatSi($sensor['sensor_limit_low'], 2, 3, $sensor->unit()));
        echo '<span class="label label-default">low: ' . $sensor_limit_low . '</span>';
    }
    if ($sensor->sensor_limit_low_warn) {
        $sensor_limit_low_warn = trim(Number::formatSi($sensor['sensor_limit_low_warn'], 2, 3, $sensor->unit()));
        echo '<span class="label label-default">low_warn: ' . $sensor_limit_low_warn . '</span>';
    }
    if ($sensor->sensor_limit_warn) {
        $sensor_limit_warn = trim(Number::formatSi($sensor['sensor_limit_warn'], 2, 3, $sensor->unit()));
        echo '<span class="label label-default">high_warn: ' . $sensor_limit_warn . '</span>';
    }
    if ($sensor->sensor_limit) {
        $sensor_limit = trim(Number::formatSi($sensor['sensor_limit'], 2, 3, $sensor->unit()));
        echo '<span class="label label-default">high: ' . $sensor_limit . '</span>';
    }

    echo '        </div></h3>' .
         "    </div>\n" .
         "    <div class='panel-body'>\n";

    $graph_array['id'] = $sensor->sensor_id;
    $graph_array['type'] = 'sensor_' . $sensor->sensor_class;

    include 'includes/html/print-graphrow.inc.php';

    echo "    </div>\n" .
         "</div>\n";
}
