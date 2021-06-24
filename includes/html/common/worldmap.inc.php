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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * Custom Frontpage
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */

use Illuminate\Support\Facades\Auth;
use LibreNMS\Alert\AlertUtil;
use LibreNMS\Config;

$install_dir = Config::get('install_dir');

if (Config::get('map.engine', 'leaflet') == 'leaflet') {
    $temp_output = '
<script src="js/leaflet.js"></script>
<script src="js/leaflet.markercluster.js"></script>
<script src="js/leaflet.awesome-markers.min.js"></script>
<div id="leaflet-map"></div>
<script>
        ';
    $init_lat = Config::get('leaflet.default_lat', 51.48);
    $init_lng = Config::get('leaflet.default_lng', 0);
    $init_zoom = Config::get('leaflet.default_zoom', 5);
    $group_radius = Config::get('leaflet.group_radius', 80);
    $tile_url = Config::get('leaflet.tile_url', '{s}.tile.openstreetmap.org');
    $show_status = [0, 1];
    $map_init = '[' . $init_lat . ', ' . $init_lng . '], ' . sprintf('%01.1f', $init_zoom);
    $temp_output .= 'var map = L.map(\'leaflet-map\', { zoomSnap: 0.1 } ).setView(' . $map_init . ');
L.tileLayer(\'//' . $tile_url . '/{z}/{x}/{y}.png\', {
    attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
}).addTo(map);

var markers = L.markerClusterGroup({
    maxClusterRadius: ' . $group_radius . ',
    iconCreateFunction: function (cluster) {
        var markers = cluster.getAllChildMarkers();
        var n = 0;
        color = "green"
        newClass = "Cluster marker-cluster marker-cluster-small leaflet-zoom-animated leaflet-clickable";
        for (var i = 0; i < markers.length; i++) {
            if (markers[i].options.icon.options.markerColor == "blue" && color != "red") {
                color = "blue";
            }
            if (markers[i].options.icon.options.markerColor == "red") {
                color = "red";
            }
        }
        return L.divIcon({ html: cluster.getChildCount(), className: color+newClass, iconSize: L.point(40, 40) });
    },
  });
var redMarker = L.AwesomeMarkers.icon({
    icon: \'server\',
    markerColor: \'red\', prefix: \'fa\', iconColor: \'white\'
  });
var blueMarker = L.AwesomeMarkers.icon({
      icon: \'server\',
      markerColor: \'blue\', prefix: \'fa\', iconColor: \'white\'
    });
var greenMarker = L.AwesomeMarkers.icon({
    icon: \'server\',
    markerColor: \'green\', prefix: \'fa\', iconColor: \'white\'
  });
        ';

    // Checking user permissions
    if (Auth::user()->hasGlobalRead()) {
        // Admin or global read-only - show all devices
        $sql = "SELECT DISTINCT(`device_id`),`location`,`sysName`,`hostname`,`os`,`status`,`lat`,`lng` FROM `devices`
                LEFT JOIN `locations` ON `devices`.`location_id`=`locations`.`id`
                WHERE `disabled`=0 AND `ignore`=0 AND ((`lat` != '' AND `lng` != '') OR (`location` REGEXP '\[[0-9\.\, ]+\]'))
                AND (`lat` IS NOT NULL AND `lng` IS NOT NULL)
                AND `status` IN " . dbGenPlaceholders(count($show_status)) .
                ' ORDER BY `status` ASC, `hostname`';
        $param = $show_status;
    } else {
        // Normal user - grab devices that user has permissions to
        $device_ids = Permissions::devicesForUser()->toArray() ?: [0];

        $sql = "SELECT DISTINCT(`devices`.`device_id`) as `device_id`,`location`,`sysName`,`hostname`,`os`,`status`,`lat`,`lng`
                FROM `devices`
                LEFT JOIN `locations` ON `devices`.location_id=`locations`.`id`
                WHERE `disabled`=0 AND `ignore`=0 AND ((`lat` != '' AND `lng` != '') OR (`location` REGEXP '\[[0-9\.\, ]+\]'))
                AND (`lat` IS NOT NULL AND `lng` IS NOT NULL)
                AND `devices`.`device_id` IN " . dbGenPlaceholders(count($device_ids)) .
                ' AND `status` IN ' . dbGenPlaceholders(count($show_status)) .
                ' ORDER BY `status` ASC, `hostname`';
        $param = array_merge($device_ids, $show_status);
    }

    foreach (dbFetchRows($sql, $param) as $map_devices) {
        $icon = 'greenMarker';
        $z_offset = 0;
        $tmp_loc = parse_location($map_devices['location']);
        if (is_numeric($tmp_loc['lat']) && is_numeric($tmp_loc['lng'])) {
            $map_devices['lat'] = $tmp_loc['lat'];
            $map_devices['lng'] = $tmp_loc['lng'];
        }
        if ($map_devices['status'] == 0) {
            if (AlertUtil::isMaintenance($map_devices['device_id'])) {
                if ($show_status == 0) { // Don't show icon if only down devices should be shown
                    continue;
                } else {
                    $icon = 'blueMarker';
                    $z_offset = 5000;
                }
            } else {
                $icon = 'redMarker';
                $z_offset = 10000;  // move marker to foreground
            }
        }
        $temp_output .= "var title = '<a href=\"" . \LibreNMS\Util\Url::deviceUrl((int) $map_devices['device_id']) . '"><img src="' . getIcon($map_devices) . '" width="32" height="32" alt=""> ' . format_hostname($map_devices) . "</a>';
var tooltip = '" . format_hostname($map_devices) . "';
var marker = L.marker(new L.LatLng(" . $map_devices['lat'] . ', ' . $map_devices['lng'] . "), {title: tooltip, icon: $icon, zIndexOffset: $z_offset});
marker.bindPopup(title);
    markers.addLayer(marker);\n";
    }

    if (Config::get('network_map_show_on_worldmap')) {
        if (Auth::user()->hasGlobalRead()) {
            $sql = "
            SELECT
              ll.id AS left_id,
              ll.lat AS left_lat,
              ll.lng AS left_lng,
              rl.id AS right_id,
              rl.lat AS right_lat,
              rl.lng AS right_lng,
              sum(lp.ifHighSpeed) AS link_capacity,
              sum(lp.ifOutOctets_rate) * 8 / sum(lp.ifSpeed) * 100 as link_out_usage_pct,
              sum(lp.ifInOctets_rate) * 8 / sum(lp.ifSpeed) * 100 as link_in_usage_pct
            FROM
              devices AS ld,
              devices AS rd,
              links AS l,
              locations AS ll,
              locations AS rl,
              ports as lp
            WHERE
              l.local_device_id = ld.device_id
              AND l.remote_device_id = rd.device_id
              AND ld.location_id != rd.location_id
              AND ld.location_id = ll.id
              AND rd.location_id = rl.id
              AND lp.device_id = ld.device_id
              AND lp.port_id = l.local_port_id
              AND lp.ifType = 'ethernetCsmacd'
              AND ld.disabled = 0
              AND ld.ignore = 0
              AND rd.disabled = 0
              AND rd.ignore = 0
              AND lp.ifOutOctets_rate != 0
              AND lp.ifInOctets_rate != 0
              AND lp.ifOperStatus = 'up'
              AND ll.lat IS NOT NULL
              AND ll.lng IS NOT NULL
              AND rl.lat IS NOT NULL
              AND rl.lng IS NOT NULL
              AND ld.status IN " . dbGenPlaceholders(count($show_status)) . '
              AND rd.status IN ' . dbGenPlaceholders(count($show_status)) . '
            GROUP BY
              left_id, right_id, ll.lat, ll.lng, rl.lat, rl.lng
                  ';
            $param = array_merge($show_status, $show_status);
        } else {
            $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
            $sql = "
            SELECT
              ll.id AS left_id,
              ll.lat AS left_lat,
              ll.lng AS left_lng,
              rl.id AS right_id,
              rl.lat AS right_lat,
              rl.lng AS right_lng,
              sum(lp.ifHighSpeed) AS link_capacity,
              sum(lp.ifOutOctets_rate) * 8 / sum(lp.ifSpeed) * 100 as link_out_usage_pct,
              sum(lp.ifInOctets_rate) * 8 / sum(lp.ifSpeed) * 100 as link_in_usage_pct
            FROM
              devices AS ld,
              devices AS rd,
              links AS l,
              locations AS ll,
              locations AS rl,
              ports as lp
            WHERE
              l.local_device_id = ld.device_id
              AND l.remote_device_id = rd.device_id
              AND ld.location_id != rd.location_id
              AND ld.location_id = ll.id
              AND rd.location_id = rl.id
              AND lp.device_id = ld.device_id
              AND lp.port_id = l.local_port_id
              AND lp.ifType = 'ethernetCsmacd'
              AND ld.disabled = 0
              AND ld.ignore = 0
              AND rd.disabled = 0
              AND rd.ignore = 0
              AND lp.ifOutOctets_rate != 0
              AND lp.ifInOctets_rate != 0
              AND lp.ifOperStatus = 'up'
              AND ll.lat IS NOT NULL
              AND ll.lng IS NOT NULL
              AND rl.lat IS NOT NULL
              AND rl.lng IS NOT NULL
              AND ld.status IN " . dbGenPlaceholders(count($show_status)) . '
              AND rd.status IN ' . dbGenPlaceholders(count($show_status)) . '
              AND ld.device_id IN ' . dbGenPlaceholders(count($device_ids)) . '
              AND rd.device_id IN ' . dbGenPlaceholders(count($device_ids)) . '
            GROUP BY
              left_id, right_id, ll.lat, ll.lng, rl.lat, rl.lng
                  ';
            $param = array_merge($show_status, $show_status, $device_ids, $device_ids);
        }

        foreach (dbFetchRows($sql, $param) as $link) {
            $icon = 'greenMarker';
            $z_offset = 0;

            $speed = $link['link_capacity'] / 1000;
            if ($speed > 500000) {
                $width = 20;
            } else {
                $width = round(0.77 * pow($speed, 0.25));
            }

            $link_used = max($link['link_out_usage_pct'], $link['link_in_usage_pct']);
            $link_used = round(2 * $link_used, -1) / 2;
            if ($link_used > 100) {
                $link_used = 100;
            }
            if (is_nan($link_used)) {
                $link_used = 0;
            }
            $link_color = Config::get("network_map_legend.$link_used");

            $temp_output .= 'var marker = new L.Polyline([new L.LatLng(' . $link['left_lat'] . ', ' . $link['left_lng'] . '), new L.LatLng(' . $link['right_lat'] . ', ' . $link['right_lng'] . ")], {
                color: '" . $link_color . "',
                weight: " . $width . ',
                opacity: 0.8,
                smoothFactor: 1
            });
            markers.addLayer(marker);
            ';
        }
    }

    $temp_output .= 'map.addLayer(markers);
map.scrollWheelZoom.disable();
$(document).ready(function(){
    $("#leaflet-map").on("click", function(event) {
        map.scrollWheelZoom.enable();
    });
    $("#leaflet-map").on("mouseleave", function(event) {
        map.scrollWheelZoom.disable();
    });
});
</script>';
} else {
    $temp_output = 'Mapael engine not supported here';
}

unset($common_output);
$common_output[] = $temp_output;
