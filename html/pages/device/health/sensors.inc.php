<?php

//echo '<table cellspacing=0 cellpadding=5 width=100%>';

$row = 1;

foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_descr`', array($class, $device['device_id'])) as $sensor) {
    if (!is_integer($row / 2)) {
        $row_colour = $list_colour_a;
    }
    else {
        $row_colour = $list_colour_b;
    }
    $sensor_descr = $sensor['sensor_descr'];
    $sensor_current = format_si($sensor['sensor_current']).$unit;
    $sensor_limit = format_si($sensor['sensor_limit']).$unit;
    $sensor_limit_low = format_si($sensor['sensor_limit_low']).$unit;
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$sensor_descr <div class='pull-right'>$sensor_current | $sensor_limit_low <> $sensor_limit</div></h3>
            </div>";
    echo "<div class='panel-body'>";

    $graph_array['id']   = $sensor['sensor_id'];
    $graph_array['type'] = $graph_type;

    include 'includes/print-graphrow.inc.php';

    echo '</div></div>';

    $row++;
}

//echo '</table>';
