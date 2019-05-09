<?php

$row = 1;

foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_descr`', array($class, $device['device_id'])) as $sensor) {
    $state_translation = array();
    if (($graph_type == 'sensor_state')) {
        $state_translation = dbFetchRows('SELECT * FROM state_translations as ST, sensors_to_state_indexes as SSI WHERE ST.state_index_id=SSI.state_index_id AND SSI.sensor_id = ? AND ST.state_value = ? ', array($sensor['sensor_id'], $sensor['sensor_current']));
    }
    if (!is_integer($row / 2)) {
        $row_colour = $config['list_colour']['even'];
    } else {
        $row_colour = $config['list_colour']['odd'];
    }

    if ($sensor['poller_type'] == "ipmi") {
        $sensor_descr = ipmiSensorName($device['hardware'], $sensor['sensor_descr']);
    } else {
        $sensor_descr = $sensor['sensor_descr'];
    }

    if (($graph_type == 'sensor_state') && !empty($state_translation['0']['state_descr'])) {
        $sensor_current = get_state_label($state_translation[0]['state_generic_value'], $state_translation[0]['state_descr'] . " (".$sensor['sensor_current'].")");
    } else {
        $current_label = get_sensor_label_color($sensor);
        $sensor_current = "<span class='label $current_label'>".trim(format_si($sensor['sensor_current']).$unit)."</span>";
    }

    $sensor_limit = trim(format_si($sensor['sensor_limit']).$unit);
    $sensor_limit_low = trim(format_si($sensor['sensor_limit_low']).$unit);
    $sensor_limit_warn = trim(format_si($sensor['sensor_limit_warn']).$unit);
    $sensor_limit_low_warn = trim(format_si($sensor['sensor_limit_low_warn']).$unit);

    echo "<div class='panel panel-default'>
        <div class='panel-heading'>
        <h3 class='panel-title'>$sensor_descr <div class='pull-right'>$sensor_current";

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

    echo "</div></h3>
        </div>";
    echo "<div class='panel-body'>";

    $graph_array['id']   = $sensor['sensor_id'];
    $graph_array['type'] = $graph_type;

    include 'includes/html/print-graphrow.inc.php';

    echo '</div></div>';

    $row++;
}
