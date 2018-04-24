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

use App\Models\WirelessSensor;
use LibreNMS\Authentication\Auth;

$graph_type = mres($vars['graph_type']);
$unit       = mres($vars['unit']);
$class      = mres($vars['class']);

/** @var \Illuminate\Database\Query\Builder $query */
$query = WirelessSensor::with('device')
    ->leftJoin('devices', 'devices.device_id', 'wireless_sensors.device_id')
    ->hasAccess(Auth::user())
    ->where('wireless_sensors.type', $vars['class']);

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $query->where(function ($query) use ($searchPhrase) {
        $query->orWhere('devices.hostname', 'like', "%$searchPhrase%")
            ->orWhere('description', 'like', "%$searchPhrase%")
            ->orWhere('value', 'like', "%$searchPhrase%");
    });
}

$count = $query->count();


if (empty($sort)) {
    $query->orderBy('hostname')->orderBy('description');
} else {
    $query->orderBy($sort);
}

if (isset($current)) {
    $offset  = (($current * $rowCount) - ($rowCount));
    $limit = $rowCount;
}

if ($rowCount != -1) {
    $query->offset($offset)->limit($limit);
}

foreach ($query->with('device')->get() as $sensor) {
    $sensor_id = $sensor->wireless_sensor_id;

    if ($sensor->inAlarm()) {
        $alert = '<i class="fa fa-flag fa-lg" style="color:red" aria-hidden="true"></i>';
    } else {
        $alert = '';
    }

    // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
    // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
    // FIXME - DUPLICATED IN device/overview/sensors
    $graph_colour = str_replace('#', '', $row_colour);

    $graph_array           = array();
    $graph_array['height'] = '100';
    $graph_array['width']  = '210';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $sensor->wireless_sensor_id;
    $graph_array['type']   = $graph_type;
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = 'no';

    $link_array         = $graph_array;
    $link_array['page'] = 'graphs';
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link_graph = generate_url($link_array);

    $link = generate_url(array('page' => 'device', 'device' => $sensor->device_id, 'tab' => $tab, 'metric' => $sensor->type));

    $overlib_content = '<div style="width: 580px;"><h2>'.$sensor->device->hostname .' - '.$sensor->description.'</h1>';
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

    $description = substr($sensor->description, 0, 48);

    $response[] = array(
        'hostname'         => generate_device_link($sensor->device->toArray()),
        'sensor_descr'     => overlib_link($link, $description, $overlib_content, null),
        'graph'            => overlib_link($link_graph, $sensor_minigraph, $overlib_content, null),
        'alert'            => $alert,
        'sensor_current'   => $sensor->value.$unit,
        'sensor_limit_low' => is_null($sensor->alert_low) ? '-' : round($sensor->alert_low, 2).$unit,
        'sensor_limit'     => is_null($sensor->alert_high) ? '-' : round($sensor->alert_high, 2).$unit,
    );

    if ($vars['view'] == 'graphs') {
        $daily_graph = 'graph.php?id='.$sensor_id.'&amp;type='.$graph_type.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=211&amp;height=100';
        $daily_url   = 'graph.php?id='.$sensor_id.'&amp;type='.$graph_type.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=400&amp;height=150';

        $weekly_graph = 'graph.php?id='.$sensor_id.'&amp;type='.$graph_type.'&amp;from='.$config['time']['week'].'&amp;to='.$config['time']['now'].'&amp;width=211&amp;height=100';
        $weekly_url   = 'graph.php?id='.$sensor_id.'&amp;type='.$graph_type.'&amp;from='.$config['time']['week'].'&amp;to='.$config['time']['now'].'&amp;width=400&amp;height=150';

        $monthly_graph = 'graph.php?id='.$sensor_id.'&amp;type='.$graph_type.'&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=211&amp;height=100';
        $monthly_url   = 'graph.php?id='.$sensor_id.'&amp;type='.$graph_type.'&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=400&amp;height=150';

        $yearly_graph = 'graph.php?id='.$sensor_id.'&amp;type='.$graph_type.'&amp;from='.$config['time']['year'].'&amp;to='.$config['time']['now'].'&amp;width=211&amp;height=100';
        $yearly_url   = 'graph.php?id='.$sensor_id.'&amp;type='.$graph_type.'&amp;from='.$config['time']['year'].'&amp;to='.$config['time']['now'].'&amp;width=400&amp;height=150';

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
