<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Config;

$graph_type = $vars['graph_type'];
$unit = $vars['unit'];
$class = $vars['class'];

$sql = " FROM `$table` AS S, `devices` AS D";

$sql .= ' WHERE S.sensor_class=? AND S.device_id = D.device_id ';
$param[] = $vars['class'];

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $sql .= ' AND `D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $sql .= ' AND (`D`.`hostname` LIKE ? OR `sensor_descr` LIKE ? OR `sensor_current` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase";
}

$count_sql = "SELECT COUNT(`sensor_id`) $sql";

$count = dbFetchCell($count_sql, $param);
if (empty($count)) {
    $count = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `S`.`sensor_descr`';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";

foreach (dbFetchRows($sql, $param) as $sensor) {
    $alert = '';
    if (! isset($sensor['sensor_current'])) {
        $sensor['sensor_current'] = 'NaN';
    } elseif ((! is_null($sensor['sensor_limit']) && $sensor['sensor_current'] >= $sensor['sensor_limit']) ||
        (! is_null($sensor['sensor_limit_low']) && $sensor['sensor_current'] <= $sensor['sensor_limit_low'])
    ) {
        $alert = '<i class="fa fa-flag fa-lg" style="color:red" aria-hidden="true"></i>';
    }

    // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
    // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
    // FIXME - DUPLICATED IN device/overview/sensors
    $graph_colour = str_replace('#', '', $row_colour);

    $graph_array = [];
    $graph_array['height'] = '100';
    $graph_array['width'] = '210';
    $graph_array['to'] = Config::get('time.now');
    $graph_array['id'] = $sensor['sensor_id'];
    $graph_array['type'] = $graph_type;
    $graph_array['from'] = Config::get('time.day');
    $graph_array['legend'] = 'no';

    $link_array = $graph_array;
    $link_array['page'] = 'graphs';
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link_graph = \LibreNMS\Util\Url::generate($link_array);

    $link = \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $sensor['device_id'], 'tab' => $group, 'metric' => $sensor['sensor_class']]);

    $overlib_content = '<div style="width: 580px;"><span class="overlib-text">' . $sensor['hostname'] . ' - ' . $sensor['sensor_descr'] . '</span>';
    $even = true;
    foreach (['day', 'week', 'month', 'year'] as $period) {
        $graph_array['from'] = Config::get("time.$period");
        if ($even) {
            $overlib_content .= '<br>';
        }
        $overlib_content .= str_replace('"', "\\'", \LibreNMS\Util\Url::graphTag($graph_array));
        $even = ! $even;
    }

    $overlib_content .= '</div>';

    $graph_array['width'] = 80;
    $graph_array['height'] = 20;
    $graph_array['bg'] = 'ffffff00';
    // the 00 at the end makes the area transparent.
    $graph_array['from'] = Config::get('time.day');
    $sensor_minigraph = \LibreNMS\Util\Url::graphTag($graph_array);

    $sensor['sensor_descr'] = substr($sensor['sensor_descr'], 0, 48);

    $sensor_current = $graph_type == 'sensor_state' ? get_state_label($sensor) : get_sensor_label_color($sensor, $translations);
    $response[] = [
        'hostname'         => generate_device_link($sensor),
        'sensor_descr'     => \LibreNMS\Util\Url::overlibLink($link, $sensor['sensor_descr'], $overlib_content),
        'graph'            => \LibreNMS\Util\Url::overlibLink($link_graph, $sensor_minigraph, $overlib_content),
        'alert'            => $alert,
        'sensor_current'   => $sensor_current,
        'sensor_limit_low' => is_null($sensor['sensor_limit_low']) ? '-' :
            '<span class=\'label label-default\'>' . trim(\LibreNMS\Util\Number::formatSi($sensor['sensor_limit_low'], 2, 3, '') . $unit) . '</span>',
        'sensor_limit'     => is_null($sensor['sensor_limit']) ? '-' :
            '<span class=\'label label-default\'>' . trim(\LibreNMS\Util\Number::formatSi($sensor['sensor_limit'], 2, 3, '') . $unit) . '</span>',
    ];

    if ($vars['view'] == 'graphs') {
        $daily_graph = 'graph.php?id=' . $sensor['sensor_id'] . '&amp;type=' . $graph_type . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . '&amp;width=211&amp;height=100';
        $daily_url = 'graph.php?id=' . $sensor['sensor_id'] . '&amp;type=' . $graph_type . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . '&amp;width=400&amp;height=150';

        $weekly_graph = 'graph.php?id=' . $sensor['sensor_id'] . '&amp;type=' . $graph_type . '&amp;from=' . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . '&amp;width=211&amp;height=100';
        $weekly_url = 'graph.php?id=' . $sensor['sensor_id'] . '&amp;type=' . $graph_type . '&amp;from=' . Config::get('time.week') . '&amp;to=' . Config::get('time.now') . '&amp;width=400&amp;height=150';

        $monthly_graph = 'graph.php?id=' . $sensor['sensor_id'] . '&amp;type=' . $graph_type . '&amp;from=' . Config::get('time.month') . '&amp;to=' . Config::get('time.now') . '&amp;width=211&amp;height=100';
        $monthly_url = 'graph.php?id=' . $sensor['sensor_id'] . '&amp;type=' . $graph_type . '&amp;from=' . Config::get('time.month') . '&amp;to=' . Config::get('time.now') . '&amp;width=400&amp;height=150';

        $yearly_graph = 'graph.php?id=' . $sensor['sensor_id'] . '&amp;type=' . $graph_type . '&amp;from=' . Config::get('time.year') . '&amp;to=' . Config::get('time.now') . '&amp;width=211&amp;height=100';
        $yearly_url = 'graph.php?id=' . $sensor['sensor_id'] . '&amp;type=' . $graph_type . '&amp;from=' . Config::get('time.year') . '&amp;to=' . Config::get('time.now') . '&amp;width=400&amp;height=150';

        $response[] = [
            'hostname'       => "<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
            <img src='$daily_graph' border=0></a> ",
            'sensor_descr'   => "<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
            <img src='$weekly_graph' border=0></a> ",
            'graph'          => "<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
            <img src='$monthly_graph' border=0></a>",
            'alert'          => '',
            'sensor_current' => "<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
            <img src='$yearly_graph' border=0></a>",
            'sensor_range'   => '',
        ];
    } //end if
}//end foreach

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $count,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
