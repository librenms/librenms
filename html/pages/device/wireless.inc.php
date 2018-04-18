<?php

use App\Models\WirelessSensor;

// this determines the order of the tabs
$types = WirelessSensor::getTypes(true, $device['device_id']);

$wireless_link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'wireless',
);

print_optionbar_start();

echo "<span style='font-weight: bold;'>Wireless</span> &#187; ";

if (!$vars['metric']) {
    $vars['metric'] = 'overview';
}

$sep = '';
echo '<span' . ($vars['metric'] == 'overview' ? ' class="pagemenu-selected"' : '') . '>';
echo generate_link('Overview', $wireless_link_array, array('metric' => 'overview'));
echo '</span>';

foreach ($types as $type_name => $type) {
    echo ' | <span';
    if ($vars['metric'] == $type) {
        echo ' class="pagemenu-selected"';
    }
    echo '>';

    echo generate_link($type['short'], $wireless_link_array, array('metric' => $type_name));

    echo '</span>';
}

print_optionbar_end();

if ($vars['metric'] == 'overview') {
    foreach ($types as $type_name => $type) {
        $text = $type['long'];
        if (!empty($type['unit'])) {
            $text .=  ' (' . $type['unit'] . ')';
        }

        $graph_title = generate_link($text, $wireless_link_array, array('metric' => $type_name));
        $graph_array['type'] = 'device_wireless_'.$type_name;

        include Config::get('install_dir') . '/html/includes/print-device-graph.php';
    }
} elseif (isset($types[$vars['metric']])) {
    $unit = $types[$vars['metric']]['unit'];
    $factor = 1;
    if ($unit == 'MHz') {
        $unit = 'Hz';
        $factor = 1000000;
    }
    $row = 0;

    $sensors = WirelessSensor::where(['type' => $vars['metric'], 'device_id' => $device['device_id']])->get();
    foreach ($sensors as $sensor) {
        if (!is_integer($row++ / 2)) {
            $row_colour = Config::get('list_colour.even');
        } else {
            $row_colour = Config::get('list_colour.odd');
        }

        if (empty($unit)) {
            $sensor_value = $sensor->value . $unit;
            $alert_high = $sensor->alert_high . $unit;
            $alert_low = $sensor->alert_low . $unit;
        } else {
            $sensor_value = format_si($sensor->value * $factor, 3) . $unit;
            $alert_high = format_si($sensor->alert_high * $factor, 3) . $unit;
            $alert_low = format_si($sensor->alert_low * $factor, 3) . $unit;
        }

        echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $sensor->description 
                    <div class='pull-right'>$sensor_value | $alert_low <> $alert_high</div>
                </h3>
            </div>";
        echo "<div class='panel-body'>";

        $graph_array['id']   = $sensor->wireless_sensor_id;
        $graph_array['type'] = 'wireless_' . $vars['metric'];

        include Config::get('install_dir') . '/html/includes/print-graphrow.inc.php';

        echo '</div></div>';
    }
}

$pagetitle[] = 'Wireless';
