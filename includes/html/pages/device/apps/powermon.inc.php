<?php

/*

LibreNMS Application for monitoring power consumption and cost

@link       https://www.upaya.net.au/
@copyright  2021 Ben Carbery
@author     Ben Carbery <yrebrac@upaya.net.au>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

$graphs = [
    'powermon_consumption'    => 'PowerMon - Consumption',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
