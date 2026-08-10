<?php

/*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <https://www.gnu.org/licenses/>.
*
* @package    LibreNMS
* @link       https://www.librenms.org
* @copyright  2016 Karl Shea, LibreNMS
* @author     Karl Shea <karl@karlshea.com>
*
*/

$app_data = $app->data ?? [];

if (isset($app_data['has_location']) && $app_data['has_location']) {
    $fix_status = 'UNKNOWN';
    if ($app_data['mode'] == 0) {
        $fix_status = 'UNKNOWN';
    } elseif ($app_data['mode'] == 1) {
        $fix_status = 'NO FIX';
    } elseif ($app_data['mode'] == 2) {
        $fix_status = '2D';
    } elseif ($app_data['mode'] == 3) {
        $fix_status = '3D';
    }

    print_optionbar_start();
    echo 'Status: ' . $fix_status . "<br>\n";
    // this will be blank if it is no fix, unknown, 2d
    if (is_numeric($app_data['altitude'])) {
        echo 'Altitude: ' . htmlspecialchars($app_data['altitude']) . " meters<br>\n";
    }
    // this will be blank if it is no fix or unknown
    if (is_numeric($app_data['latitude']) && is_numeric($app_data['longitude'])) {
        echo 'Lat / Lng: ' . htmlspecialchars($app_data['latitude']) . ', ' . htmlspecialchars($app_data['longitude']) . "<br>\n";
    }
    print_optionbar_end();
}

$graphs = [
    'gpsd_satellites' => 'Satellites',
    'gpsd_dop' => 'Dilution of Precision',
    'gpsd_mode' => 'Fix type :: 0=unknown, 1=no fix, 2=2D, 3=3D',
];

if (isset($app_data['has_location']) && $app_data['has_location']) {
    $graphs['gpsd_altitude'] = 'Altitude';
    $graphs['gpsd_location'] = 'Location';
    $graphs['gpsd_latitude'] = 'Latitude';
    $graphs['gpsd_longitude'] = 'longitude';
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = App\Facades\LibrenmsConfig::get('time.now');
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
