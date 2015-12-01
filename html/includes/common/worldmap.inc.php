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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Custom Frontpage
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Frontpage
 */

if ($config['map']['engine'] == 'leaflet') {
    if (defined('show_settings') && $config['front_page'] == "pages/front/tiles.php") {
        $temp_output = '
<form class="form" onsubmit="widget_settings(this); return false;">
  <div class="form-group">
    <div class="col-sm-4">
      <label for="init_lat" class="control-label">Initial Latitude: </label>
    </div>
    <div class="col-sm-8">
      <input class="form-control" name="init_lat" id="input_lat_'.$unique_id.'" value="'.$widget_settings['init_lat'].'" placeholder="ie. 51.4800 for Greenwich">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-4">
      <label for="init_lng" class="control-label">Initial Longitude: </label>
    </div>
    <div class="col-sm-6">
      <input class="form-control" name="init_lng" id="input_lng_'.$unique_id.'" value="'.$widget_settings['init_lng'].'" placeholder="ie. 0 for Greenwich">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-4">
      <label for="init_zoom" class="control-label">Initial Zoom: </label>
    </div>
    <div class="col-sm-8">
      <select class="form-control" name="init_zoom" id="select_zoom'.$unique_id.'">
        ';
        for ($i=0; $i<19; $i++) {
    	if ($i == $widget_settings['init_zoom']) {
                $temp_output .= '<option selected value="'.$i.'">'.$i.'</option>';
            }
            else {
                $temp_output .= '<option value="'.$i.'">'.$i.'</option>';
            }
        }
        $temp_output .= '
      </select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-4">
      <label for="group_radius" class="control-label">Grouping radius: </label>
    </div>
    <div class="col-sm-8">
      <input class="form-control" name="group_radius" id="input_radius_'.$unique_id.'" value="'.$widget_settings['group_radius'].'" placeholder="default 80">
    </div>
  </div>
    <div class="form-group">
        <div class="col-sm-4">
            <label for="status" class="control-label">Show devices: </label>
        </div>
        <div class="col-sm-8">
            <select class="form-control" name="status" id="status_'.$unique_id.'">';

                $temp_output .= '<option value="0,1"';
                if ($widget_settings['status'] == '0,1') {
                    $temp_output .= ' selected';
                }
                $temp_output .= '>Up + Down</option>
                <option value="1"';
                if ($widget_settings['status'] == '1') {
                    $temp_output .= ' selected';
                }
                $temp_output .= '>Up</option>
                <option value="0"';
                if ($widget_settings['status'] == '0') {
                    $temp_output .= ' selected';
                }
                $temp_output .= '>Down</option>
            </select>
        </div>
    </div>
  <div class="form-group">
    <div class="col-sm-2">
      <button type="submit" class="btn btn-default">Set</button>
    </div>
  </div>
</form>
        ';
    }
    else {
        $temp_output = '
<script src="js/leaflet.js"></script>
<script src="js/leaflet.markercluster-src.js"></script>
<script src="js/leaflet.awesome-markers.min.js"></script>
<div id="leaflet-map"></div>
<script>
        ';
        if (!empty($widget_settings) && !empty($widget_settings['init_lat']) && !empty($widget_settings['init_lng'])) {
            $init_lat = $widget_settings['init_lat'];
            $init_lng = $widget_settings['init_lng'];
            $init_zoom = $widget_settings['init_zoom'];
        }
        elseif (isset($config['leaflet'])) {
            $init_lat = $config['leaflet']['default_lat'];
            $init_lng = $config['leaflet']['default_lng'];
            $init_zoom = $config['leaflet']['default_zoom'];
        }
        if (!empty($widget_settings['group_radius'])) {
            $group_radius = $widget_settings['group_radius'];
        }
        else {
            $group_radius = 80;
        }
        if (empty($widget_settings['status']) && $widget_settings['status'] != '0') {
            $widget_settings['status'] = '0,1';
        }
        $map_init = "[" . $init_lat . ", " . $init_lng . "], " . sprintf("%01.0f", $init_zoom);
        $temp_output .= 'var map = L.map(\'leaflet-map\').setView('.$map_init.');
L.tileLayer(\'//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
    attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
}).addTo(map);

var markers = L.markerClusterGroup({
    maxClusterRadius: ' . $group_radius . ',
  });
var redMarker = L.AwesomeMarkers.icon({
    icon: \'server\',
    markerColor: \'red\', prefix: \'fa\', iconColor: \'white\'
  });
var greenMarker = L.AwesomeMarkers.icon({
    icon: \'server\',
    markerColor: \'green\', prefix: \'fa\', iconColor: \'white\'
  });
        ';
        // Checking user permissions
        if (is_admin() || is_read()) {
        // Admin or global read-only - show all devices
            $sql = "SELECT DISTINCT(`device_id`),`devices`.`location`,`hostname`,`os`,`status`,`lat`,`lng` FROM `devices`
                    LEFT JOIN `locations` ON `devices`.`location`=`locations`.`location`
                    WHERE `disabled`=0 AND `ignore`=0 AND ((`lat` != '' AND `lng` != '') OR (`devices`.`location` REGEXP '\[[0-9\.\, ]+\]'))
                      AND `status` IN (".$widget_settings['status'].")
                    ORDER BY `status` ASC, `hostname`";
        }
        else {
        // Normal user - grab devices that user has permissions to
            $sql = "SELECT DISTINCT(`devices`.`device_id`) as `device_id`,`devices`.`location`,`hostname`,`os`,`status`,`lat`,`lng`
                    FROM `devices_perms`, `devices`
                    LEFT JOIN `locations` ON `devices`.`location`=`locations`.`location`
                    WHERE `disabled`=0 AND `ignore`=0 AND ((`lat` != '' AND `lng` != '') OR (`devices`.`location` REGEXP '\[[0-9\.\, ]+\]'))
                    AND `devices`.`device_id` = `devices_perms`.`device_id`
                    AND `devices_perms`.`user_id` = ? AND `status` IN (".$widget_settings['status'].")
                    ORDER BY `status` ASC, `hostname`";
            $param[] = $_SESSION['user_id'];
        }
        foreach (dbFetchRows($sql, $param) as $map_devices) {
            $icon = 'greenMarker';
            $z_offset = 0;
            $tmp_loc = parse_location($map_devices['location']);
            if (!empty($tmp_loc['lat']) && !empty($tmp_loc['lng'])) {
                $map_devices['lat'] = $tmp_loc['lat'];
                $map_devices['lng'] = $tmp_loc['lng'];
            }
            if ($map_devices['status'] == 0) {
                $icon = 'redMarker';
                $z_offset = 10000;  // move marker to foreground
            }
            $temp_output .= "var title = '<a href=\"" . generate_device_url($map_devices) . "\"><img src=\"".getImageSrc($map_devices)."\" width=\"32\" height=\"32\" alt=\"\">".$map_devices['hostname']."</a>';
var marker = L.marker(new L.LatLng(".$map_devices['lat'].", ".$map_devices['lng']."), {title: title, icon: $icon, zIndexOffset: $z_offset});
marker.bindPopup(title);
    markers.addLayer(marker);\n";
        }
        $temp_output .= 'map.addLayer(markers);
map.scrollWheelZoom.disable();
$(document).ready(function(){
    $("#leaflet-map").on("click", function(event) {  
        map.scrollWheelZoom.enable();
    });
    $("#leaflet-map").mouseleave(function(event) {  
        map.scrollWheelZoom.disable();
    });
});
</script>';
    }
}
else {
    $temp_output = 'Mapael engine not supported here';
}

unset($common_output);
$common_output[] = $temp_output;
