<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * Custom Frontpage
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Frontpage
 */

$temp_output .= "
<script type='text/javascript'>
    google.load('visualization', '1', {'packages': ['geochart'], callback: function() {

    drawRegionsMap();
    function drawRegionsMap() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Site');
        data.addColumn('number', 'Status');
        data.addColumn('number', 'Size');
        data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
        data.addRows([
";

$locations = array();
foreach (getlocations() as $location_row) {
    $location = $location_row['location'];
    $location_id = $location_row['id'];
    $devices = array();
    $devices_down = array();
    $devices_up = array();
    $count = 0;
    $down  = 0;
    foreach (dbFetchRows("SELECT `device_id`, `hostname`, `status` FROM `devices` WHERE `location_id` = ? && `disabled` = 0 && `ignore` = 0 GROUP BY `hostname`", [$location_id]) as $device) {
        if (\LibreNMS\Config::get('frontpage_globe.markers') == 'devices' || empty(\LibreNMS\Config::get('frontpage_globe.markers'))) {
            $devices[] = $device['hostname'];
            $count++;
            if ($device['status'] == "0") {
                $down++;
                $devices_down[] = $device['hostname']." DOWN";
            } else {
                $devices_up[] = $device;
            }
        } elseif (\LibreNMS\Config::get('frontpage_globe.markers') == 'ports') {
            foreach (dbFetchRows("SELECT ifName,ifOperStatus,ifAdminStatus FROM ports WHERE ports.device_id = ? && ports.ignore = 0 && ports.disabled = 0 && ports.deleted = 0", array($device['device_id'])) as $port) {
                $count++;
                if ($port['ifOperStatus'] == 'down' && $port['ifAdminStatus'] == 'up') {
                    $down++;
                    $devices_down[] = $device['hostname']."/".$port['ifName']." DOWN";
                } else {
                    $devices_up[] = $port;
                }
            }
        }
    }
    $pdown = $count ? ($down / $count)*100 : 0;
    if (\LibreNMS\Config::get('frontpage_globe.markers') == 'devices' || empty(\LibreNMS\Config::get('frontpage_globe.markers'))) {
        $devices_down = array_merge(array(count($devices_up). " Devices OK"), $devices_down);
    } elseif (\LibreNMS\Config::get('frontpage_globe.markers') == 'ports') {
        $devices_down = array_merge(array(count($devices_up). " Ports OK"), $devices_down);
    }
    $locations[] = "            ['".$location."', ".$pdown.", ".$count.", '".implode(",<br/> ", $devices_down)."']";
}
$temp_output .= implode(",\n", $locations);

$map_world = \LibreNMS\Config::get('frontpage_globe.region', 'world');
$map_countries = \LibreNMS\Config::get('frontpage_globe.resolution', 'countries');

$temp_output .= "
        ]);
        var options = {
            region: '". $map_world ."',
            resolution: '". $map_countries ."',
            displayMode: 'markers',
            keepAspectRatio: 1,
            magnifyingGlass: {enable: true, zoomFactor: 100},
            colorAxis: {minValue: 0,  maxValue: 100, colors: ['green', 'yellow', 'red']},
            markerOpacity: 0.90,
            tooltip: {isHtml: true},
        };
        var chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    };
    }
});
</script>
<div id='chart_div'></div>
";

unset($common_output);
$common_output[] = $temp_output;
