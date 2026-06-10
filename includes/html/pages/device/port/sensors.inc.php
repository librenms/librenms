<?php

use LibreNMS\Enum\Severity;
use LibreNMS\Util\Html;

$sensors = \App\Models\Sensor::where('device_id', $device['device_id'])
    ->where('entPhysicalIndex', $port['ifIndex'])
    ->where('entPhysicalIndex_measured', 'ports')
    ->orderBy('sensor_type')
    ->get();

foreach ($sensors as $sensor) {
    if ($sensor->poller_type == 'ipmi') {
        $sensor_descr = ipmiSensorName($device['hardware'], $sensor->sensor_descr);
    } else {
        $sensor_descr = $sensor->sensor_descr;
    }

    $sensor_current = Html::severityToLabel($sensor->currentStatus(), $sensor->formatValue());

    echo "<div class='panel panel-default'>\n" .
         "    <div class='panel-heading'>\n" .
         "        <h3 class='panel-title'>$sensor_descr <div class='pull-right'>$sensor_current";

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

    echo '        </div></h3>' .
         "    </div>\n" .
         "    <div class='panel-body'>\n";

    $graph_array['id'] = $sensor->sensor_id;
    $graph_array['type'] = 'sensor_' . $sensor->sensor_class;

    include 'includes/html/print-graphrow.inc.php';

    echo "    </div>\n" .
         "</div>\n";
}
