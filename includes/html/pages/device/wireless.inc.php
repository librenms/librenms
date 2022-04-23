<?php

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Util\Number;

// this determines the order of the tabs
$types = WirelessSensor::getTypes();

$sensors = dbFetchColumn(
    'SELECT `sensor_class` FROM `wireless_sensors` WHERE `device_id` = ? GROUP BY `sensor_class`',
    [$device['device_id']]
);
$datas = array_intersect(array_keys($types), $sensors);

$wireless_link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'wireless',
];

print_optionbar_start();

echo "<span style='font-weight: bold;'>Wireless</span> &#187; ";

if (! $vars['metric']) {
    $vars['metric'] = 'overview';
}

$sep = '';
echo '<span' . ($vars['metric'] == 'overview' ? ' class="pagemenu-selected"' : '') . '>';
echo generate_link('Overview', $wireless_link_array, ['metric' => 'overview']);
echo '</span>';

foreach ($datas as $type) {
    echo ' | <span';
    if ($vars['metric'] == $type) {
        echo ' class="pagemenu-selected"';
    }
    echo '>';

    echo generate_link(__("wireless.$type.short"), $wireless_link_array, ['metric' => $type]);

    echo '</span>';
}

print_optionbar_end();

if ($vars['metric'] == 'overview') {
    foreach ($datas as $type) {
        $text = __("wireless.$type.long");
        $unit = __("wireless.$type.unit");
        if (! empty($unit)) {
            $text .= " ($unit)";
        }

        $graph_title = generate_link($text, $wireless_link_array, ['metric' => $type]);
        $graph_array['type'] = 'device_wireless_' . $type;

        include \LibreNMS\Config::get('install_dir') . '/includes/html/print-device-graph.php';
    }
} elseif (isset($types[$vars['metric']])) {
    $unit = __('wireless.' . $vars['metric'] . '.unit');
    $factor = 1;
    if ($unit == 'MHz') {
        $unit = 'Hz';
        $factor = 1000000;
    }
    $row = 0;

    $sensors = dbFetchRows(
        'SELECT * FROM `wireless_sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_descr`',
        [$vars['metric'], $device['device_id']]
    );
    foreach ($sensors as $sensor) {
        if (! is_integer($row++ / 2)) {
            $row_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $row_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        $sensor_descr = $sensor['sensor_descr'];

        if (empty($unit)) {
            $sensor_current = ((int) $sensor['sensor_current']) . $unit;
            $sensor_limit = ((int) $sensor['sensor_limit']) . $unit;
            $sensor_limit_low = ((int) $sensor['sensor_limit_low']) . $unit;
        } else {
            $sensor_current = Number::formatSi($sensor['sensor_current'] * $factor, 3, 3, $unit);
            $sensor_limit = Number::formatSi($sensor['sensor_limit'] * $factor, 3, 3, $unit);
            $sensor_limit_low = Number::formatSi($sensor['sensor_limit_low'] * $factor, 3, 3, $unit);
        }

        echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $sensor_descr
                    <div class='pull-right'>$sensor_current | $sensor_limit_low <> $sensor_limit</div>
                </h3>
            </div>";
        echo "<div class='panel-body'>";

        $graph_array['id'] = $sensor['sensor_id'];
        $graph_array['type'] = 'wireless_' . $vars['metric'];

        include \LibreNMS\Config::get('install_dir') . '/includes/html/print-graphrow.inc.php';

        echo '</div></div>';
    }
}

$pagetitle[] = 'Wireless';
