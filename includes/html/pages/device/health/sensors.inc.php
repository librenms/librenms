<?php

use LibreNMS\Enum\Severity;
use LibreNMS\Util\Html;

$row = 0;

$sensors = \App\Models\Sensor::where('sensor_class', $class)->where('device_id', $device['device_id'])->orderBy('sensor_descr')->get();

foreach ($sensors as $sensor) {
    if (! is_integer($row++ / 2)) {
        $row_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $row_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    if ($sensor['poller_type'] == 'ipmi') {
        $sensor_descr = ipmiSensorName($device['hardware'], $sensor['sensor_descr']);
    } else {
        $sensor_descr = $sensor['sensor_descr'];
    }

    $sensor_current = Html::severityToLabel($sensor->currentStatus(), $sensor->formatValue());

    echo "<div class='panel panel-default'>
        <div class='panel-heading'>
        <h3 class='panel-title'>$sensor_descr <div class='pull-right'>$sensor_current";

    //Display low and high limit if they are not null (format_si() is changing null to '0')
    if (! is_null($sensor->sensor_limit_low)) {
        echo ' ' . Html::severityToLabel(Severity::Unknown, 'low: ' . $sensor->formatValue('sensor_limit_low'));
    }
    if (! is_null($sensor->sensor_limit_low_warn)) {
        echo ' ' . Html::severityToLabel(Severity::Unknown, 'low_warn: ' . $sensor->formatValue('sensor_limit_low_warn'));
    }
    if (! is_null($sensor->sensor_limit_warn)) {
        echo ' ' . Html::severityToLabel(Severity::Unknown, 'high_warn: ' . $sensor->formatValue('sensor_limit_warn'));
    }
    if (! is_null($sensor->sensor_limit)) {
        echo ' ' . Html::severityToLabel(Severity::Unknown, 'high: ' . $sensor->formatValue('sensor_limit'));
    }

    echo '</div></h3>
        </div>';
    echo "<div class='panel-body'>";

    $graph_array['id'] = $sensor['sensor_id'];
    $graph_array['type'] = $graph_type;

    include 'includes/html/print-graphrow.inc.php';

    echo '</div></div>';
}
