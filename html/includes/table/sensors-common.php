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
 * @link       http://librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Authentication\LegacyAuth;

$graph_type = mres($vars['graph_type']);
$unit       = mres($vars['unit']);
$class      = mres($vars['class']);

$sql = " FROM `$table` AS S, `devices` AS D";

if (!LegacyAuth::user()->hasGlobalRead()) {
    $sql .= ', devices_perms as P';
}

$sql .= " WHERE S.sensor_class=? AND S.device_id = D.device_id ";
$param[] = mres($vars['class']);

if (!LegacyAuth::user()->hasGlobalRead()) {
    $sql .= " AND D.device_id = P.device_id AND P.user_id = ?";
    $param[] = LegacyAuth::id();
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE '%$searchPhrase%' OR `sensor_descr` LIKE '%$searchPhrase%' OR `sensor_current` LIKE '%searchPhrase')";
}

$count_sql = "SELECT COUNT(`sensor_id`) $sql";

$count = dbFetchCell($count_sql, $param);
if (empty($count)) {
    $count = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = '`D`.`hostname`, `S`.`sensor_descr`';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT * $sql";

foreach (dbFetchRows($sql, $param) as $sensor) {
    $alert = '';
    if (!isset($sensor['sensor_current'])) {
        $sensor['sensor_current'] = 'NaN';
    } elseif ((!is_null($sensor['sensor_limit']) && $sensor['sensor_current'] >= $sensor['sensor_limit']) ||
        (!is_null($sensor['sensor_limit_low']) && $sensor['sensor_current'] <= $sensor['sensor_limit_low'])
    ) {
        $alert = '<i class="fa fa-flag fa-lg" style="color:red" aria-hidden="true"></i>';
    }

    // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
    // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
    // FIXME - DUPLICATED IN device/overview/sensors
    $graph_colour = str_replace('#', '', $row_colour);

    $graph_array           = array();
    $graph_array['height'] = '100';
    $graph_array['width']  = '210';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $sensor['sensor_id'];
    $graph_array['type']   = $graph_type;
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = 'no';

    $link_array         = $graph_array;
    $link_array['page'] = 'graphs';
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link_graph = generate_url($link_array);

    $link = generate_url(array('page' => 'device', 'device' => $sensor['device_id'], 'tab' => $tab, 'metric' => $sensor['sensor_class']));

    $overlib_content = '<div style="width: 580px;"><h2>'.$sensor['hostname'].' - '.$sensor['sensor_descr'].'</h1>';
    foreach (array('day', 'week', 'month', 'year') as $period) {
        $graph_array['from'] = $config['time'][$period];
        $overlib_content    .= str_replace('"', "\\'", generate_graph_tag($graph_array));
    }

    $overlib_content .= '</div>';

    $graph_array['width']  = 80;
    $graph_array['height'] = 20;
    $graph_array['bg']     = 'ffffff00';
    // the 00 at the end makes the area transparent.
    $graph_array['from'] = $config['time']['day'];
    $sensor_minigraph =  generate_lazy_graph_tag($graph_array);

    $sensor['sensor_descr'] = substr($sensor['sensor_descr'], 0, 48);

    $response[] = array(
        'hostname'         => generate_device_link($sensor),
        'sensor_descr'     => overlib_link($link, $sensor['sensor_descr'], $overlib_content, null),
        'graph'            => overlib_link($link_graph, $sensor_minigraph, $overlib_content, null),
        'alert'            => $alert,
        'sensor_current'   => $sensor['sensor_current'].$unit,
        'sensor_limit_low' => is_null($sensor['sensor_limit_low']) ? '-' : round($sensor['sensor_limit_low'], 2).$unit,
        'sensor_limit'     => is_null($sensor['sensor_limit']) ? '-' : round($sensor['sensor_limit'], 2).$unit,
    );

    if ($vars['view'] == 'graphs') {
        $daily_graph = 'graph.php?id='.$sensor['sensor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=211&amp;height=100';
        $daily_url   = 'graph.php?id='.$sensor['sensor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=400&amp;height=150';

        $weekly_graph = 'graph.php?id='.$sensor['sensor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['week'].'&amp;to='.$config['time']['now'].'&amp;width=211&amp;height=100';
        $weekly_url   = 'graph.php?id='.$sensor['sensor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['week'].'&amp;to='.$config['time']['now'].'&amp;width=400&amp;height=150';

        $monthly_graph = 'graph.php?id='.$sensor['sensor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=211&amp;height=100';
        $monthly_url   = 'graph.php?id='.$sensor['sensor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=400&amp;height=150';

        $yearly_graph = 'graph.php?id='.$sensor['sensor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['year'].'&amp;to='.$config['time']['now'].'&amp;width=211&amp;height=100';
        $yearly_url   = 'graph.php?id='.$sensor['sensor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['year'].'&amp;to='.$config['time']['now'].'&amp;width=400&amp;height=150';

        $response[] = array(
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
        );
    } //end if
}//end foreach

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $count,
);
echo _json_encode($output);
