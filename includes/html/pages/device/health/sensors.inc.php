<?php

$row = 1;

foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_descr`', [$class, $device['device_id']]) as $sensor) {
    if (! is_integer($row / 2)) {
        $row_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $row_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    if ($sensor['poller_type'] == 'ipmi') {
        $sensor_descr = ipmiSensorName($device['hardware'], $sensor['sensor_descr']);
    } else {
        $sensor_descr = $sensor['sensor_descr'];
    }

    $sensor_current = $graph_type == 'sensor_state' ? get_state_label($sensor) : get_sensor_label_color($sensor);

    $sensor_limit = trim(\LibreNMS\Util\Number::formatSi($sensor['sensor_limit'], 2, 3, '') . $unit);
    $sensor_limit_low = trim(\LibreNMS\Util\Number::formatSi($sensor['sensor_limit_low'], 2, 3, '') . $unit);
    $sensor_limit_warn = trim(\LibreNMS\Util\Number::formatSi($sensor['sensor_limit_warn'], 2, 3, '') . $unit);
    $sensor_limit_low_warn = trim(\LibreNMS\Util\Number::formatSi($sensor['sensor_limit_low_warn'], 2, 3, '') . $unit);

    echo "<div class='panel panel-default'>
        <div class='panel-heading'>
        <h3 class='panel-title'>$sensor_descr <div class='pull-right'>$sensor_current";

    //Display low and high limit if they are not null (format_si() is changing null to '0')
    if (! is_null($sensor['sensor_limit_low'])) {
        echo " <span class='label label-default'>low: $sensor_limit_low</span>";
    }
    if (! is_null($sensor['sensor_limit_low_warn'])) {
        echo " <span class='label label-default'>low_warn: $sensor_limit_low_warn</span>";
    }
    if (! is_null($sensor['sensor_limit_warn'])) {
        echo " <span class='label label-default'>high_warn: $sensor_limit_warn</span>";
    }
    if (! is_null($sensor['sensor_limit'])) {
        echo " <span class='label label-default'>high: $sensor_limit</span>";
    }

    echo '</div></h3>
        </div>';
    echo "<div class='panel-body'>";

    $graph_array['id'] = $sensor['sensor_id'];
    $graph_array['type'] = $graph_type;

    include 'includes/html/print-graphrow.inc.php';

    echo '</div></div>';

    $row++;
}
