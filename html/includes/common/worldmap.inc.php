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

$temp_output = '
<script src="js/leaflet.js"></script>
<script src="js/leaflet.markercluster-src.js"></script>
<script src="js/leaflet.awesome-markers.min.js"></script>
<div id="leaflet-map"></div>
<script>
';

$map_init = "[" . $config['leaflet']['default_lat'] . ", " . $config['leaflet']['default_lng'] . "], " . sprintf("%01.0f", $config['leaflet']['default_zoom']);

$temp_output .= 'var map = L.map(\'leaflet-map\').setView('.$map_init.');

L.tileLayer(\'//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
    attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
}).addTo(map);

var markers = L.markerClusterGroup();
var redMarker = L.AwesomeMarkers.icon({
    icon: \'server\',
    markerColor: \'red\', prefix: \'fa\', iconColor: \'white\'
  });
var greenMarker = L.AwesomeMarkers.icon({
    icon: \'server\',
    markerColor: \'green\', prefix: \'fa\', iconColor: \'white\'
  });
';

foreach (dbFetchRows("SELECT `device_id`,`hostname`,`os`,`status`,`lat`,`lng` FROM `devices` LEFT JOIN `locations` ON `devices`.`location`=`locations`.`location` WHERE `disabled`=0 AND `ignore`=0 AND `lat` != '' AND `lng` != '' ORDER BY `status` ASC, `hostname`") as $map_devices) {
    $icon = 'greenMarker';
    if ($map_devices['status'] == 0) {
        $icon = 'redMarker';
    }

    $temp_output .= "var title = '<a href=\"" . generate_device_url($map_devices) . "\"><img src=\"".getImageSrc($map_devices)."\" width=\"32\" height=\"32\" alt=\"\">".$map_devices['hostname']."</a>';
         var marker = L.marker(new L.LatLng(".$map_devices['lat'].", ".$map_devices['lng']."), {title: title, icon: $icon});
         marker.bindPopup(title);
         markers.addLayer(marker);\n";
}

$temp_output .= 'map.addLayer(markers);
</script>';

} else {
    $temp_output = 'Mapael engine not supported here';
}

unset($common_output);
$common_output[] = $temp_output;
