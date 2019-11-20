<?php

$sensors = dbFetchRows("SELECT * FROM `sensors` WHERE `device_id` = ? AND `entPhysicalIndex` = ? AND entPhysicalIndex_measured = 'ports' ORDER BY `sensor_type` ASC", array($device['device_id'],$port['ifIndex']));

foreach ($sensors as $sensor) {
    $unit = __('sensors.' . $sensor['sensor_class'] . '.unit');

    if ($sensor['poller_type'] == 'ipmi') {
        $sensor_descr = ipmiSensorName($device['hardware'], $sensor['sensor_descr']);
    } else {
        $sensor_descr = $sensor['sensor_descr'];
    }

    $sensor_current = $graph_type == 'sensor_state' ? get_state_label($sensor) : get_sensor_label_color($sensor);

    $sensor_limit = trim(format_si($sensor['sensor_limit']) . $unit);
    $sensor_limit_low = trim(format_si($sensor['sensor_limit_low']) . $unit);
    $sensor_limit_warn = trim(format_si($sensor['sensor_limit_warn']) . $unit);
    $sensor_limit_low_warn = trim(format_si($sensor['sensor_limit_low_warn']) . $unit);

    echo "<div class='panel panel-default'>\n" .
         "    <div class='panel-heading'>\n" .
         "        <h3 class='panel-title'>$sensor_descr <div class='pull-right'>$sensor_current";

    //Display low and high limit if they are not null (format_si() is changing null to '0')
    if (!is_null($sensor['sensor_limit_low'])) {
        echo " <span class='label label-default'>low: $sensor_limit_low</span>";
    }
    if (!is_null($sensor['sensor_limit_low_warn'])) {
        echo " <span class='label label-default'>low_warn: $sensor_limit_low_warn</span>";
    }
    if (!is_null($sensor['sensor_limit_warn'])) {
        echo " <span class='label label-default'>high_warn: $sensor_limit_warn</span>";
    }
    if (!is_null($sensor['sensor_limit'])) {
        echo " <span class='label label-default'>high: $sensor_limit</span>";
    }

    echo "        </div></h3>" .
         "    </div>\n" .
         "    <div class='panel-body'>\n";

    $graph_array['id']   = $sensor['sensor_id'];
    $graph_array['type'] = 'sensor_' . $sensor['sensor_class'];

    include 'includes/html/print-graphrow.inc.php';

    echo "    </div>\n" .
         "</div>\n";
}
