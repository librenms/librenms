<?php

require_once 'includes/modal/new_alert_rule.inc.php';

echo '<table class="table table-condensed table-hover">';

$row = 1;

foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_descr`', array($class, $device['device_id'])) as $sensor) {

    echo "<tr>
          <td>".$sensor['sensor_descr']."</td>
          <td>".$sensor['sensor_type']."</td>
          <td>". gen_alert_button('xs', $device, 'sensor', $sensor['sensor_id']) ."</td>
          <td>".format_si($sensor['sensor_current']).$unit."</td>
          <td>".format_si($sensor['sensor_limit']).$unit."</td>
          <td>".format_si($sensor['sensor_limit_low']).$unit."</td>
        </tr>\n";
    echo "<tr><td colspan='6'>";

    $graph_array['id']   = $sensor['sensor_id'];
    $graph_array['type'] = $graph_type;

    include 'includes/print-graphrow.inc.php';

    echo '</td></tr>';

    $row++;
}

echo '</table>';
