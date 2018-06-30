<?php

$sensors = dbFetchRows("SELECT * FROM `sensors` WHERE `device_id` = ? AND `entPhysicalIndex` = ? AND entPhysicalIndex_measured = 'ports' ORDER BY `sensor_type` ASC", array($device['device_id'],$port['ifIndex']));

$row = 0;
foreach ($sensors as $sensor) {
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

    $sensor_current = format_si($sensor['sensor_current']).$unit;
    $sensor_limit = format_si($sensor['sensor_limit']).$unit;
    $sensor_limit_low = format_si($sensor['sensor_limit_low']).$unit;
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$sensor_descr <div class='pull-right'>$sensor_current | $sensor_limit_low <> $sensor_limit</div></h3>
            </div>";
    echo "<div class='panel-body'>";

    $graph_array['id']   = $sensor['sensor_id'];
    $graph_array['type'] = "sensor_" . $sensor['sensor_class'];

    include 'includes/print-graphrow.inc.php';
    echo '</div></div>';

    $row++;
}
unset($row);
