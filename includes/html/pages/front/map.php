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

use LibreNMS\Config;

if (Config::get('map.engine') == 'leaflet') {
    require_once 'includes/html/common/worldmap.inc.php';
    echo implode('', $common_output);
} else {
    if (Config::has('mapael.default_map') && is_file(Config::get('html_dir') . '/js/' . Config::get('mapael.default_map'))) {
        $default_map = Config::get('mapael.default_map');
    } else {
        $default_map = 'maps/world_countries.js';
    }
    $map_tmp = preg_split("/\//", $default_map);
    $map_name = $map_tmp[count($map_tmp)-1];
    $map_name = str_replace('.js', '', $map_name);

    $map_width = (int)Config::get('mapael.map_width', 800);
    $default_zoom = Config::get('mapael.default_zoom', 0);


    if (Config::has('mapael.default_lat') && Config::has('mapael.default_lng')) {
        $init_zoom = "init: {
                            latitude: " . Config::has('mapael.default_lat') . ",
                            longitude: " . Config::has('mapael.default_lng') . ",
                            level: $default_zoom
                        }\n";
    }



?>
<script>
</script>
<script src='js/raphael-min.js'></script>
<script src='js/jquery.mapael.js'></script>
<script src='js/<?php echo $default_map; ?>'></script>
<script src='js/jquery.mousewheel.min.js'></script>
<?php
$x=0;
foreach (dbFetchRows("SELECT `hostname`,`location`,`status`, COUNT(`status`) AS `total`,`lat`,`lng` FROM `devices` LEFT JOIN `locations` ON `devices`.location_id=`locations`.`id` WHERE `disabled`=0 AND `ignore`=0 AND `lat` != '' AND `lng` != '' GROUP BY `status`,`lat`,`lng` ORDER BY `status` ASC, `hostname`") as $map_devices) {
    $color = "#29FF3B";
    $size = 15;
    $status = 'Up';
    if ($map_devices['status'] == 0) {
        $color = "#FF0000";
        $size = 30;
        $status = 'Down';
    }
    $data .= "\"$x\": {
                        value: \"" . $map_devices['total'] . "\",
                        latitude: ". $map_devices['lat'] . ",
                        longitude: " . $map_devices['lng'] . ",
                        size: " . $size . ",
                        attrs: {
                            fill: \"" . $color . "\",
                            opacity: 0.8
                        },
                        tooltip: {
                            content: \"Devices " . $status . ": " . $map_devices['total']  . "\"
                        }
                    },\n";
    $x++;
}
?>
<script>
$(function () {
    $(".mapcontainer").mapael({
        map: {
            name: "<?php echo $map_name; ?>",
            width: <?php echo $map_width; ?>,
            zoom : {
                enabled : true,
                maxLevel : <?php echo $default_zoom+10; ?>,
                <?php echo $init_zoom; ?>
            },
            defaultArea: {
                attrs: {
                    fill : "#449603",
                    stroke: "#295C00"
                },
                attrsHover: {
                    fill: "#367504",
                    stroke: "#5d5d5d",
                        "stroke-width": 1,
                        "stroke-linejoin": "round"
                }
            }
        },
        plots: {
            <?php echo $data; ?>
        }
    });
});
</script>
<div class="container">
        <div class="mapcontainer">
            <div class="map">
                <span>Alternative content for the map</span>
            </div>
        </div>
</div>
<?php
}
include_once 'includes/html/object-cache.inc.php';
echo '<div class="container-fluid">
	<div class="row">
		<div class="col-md-4">';
            include_once 'includes/html/front/boxes.inc.php';
echo '		</div>
                <div class="col-md-2">
                </div>
		<div class="col-md-4">';
            include_once 'includes/html/common/device-summary-vert.inc.php';
                        echo implode('', $common_output);
echo '		</div>
	</div>
	<div class="row">
		<div class="col-md-12">';
            $device['device_id'] = '-1';
            require_once 'includes/html/common/alerts.inc.php';
                        echo implode('', $common_output);
            unset($device['device_id']);
echo '		</div>
	</div>
</div>';

//From default.php - This code is not part of above license.
if (Config::get('enable_syslog')) {
    $sql = "SELECT *, DATE_FORMAT(timestamp, '" . Config::get('dateformat.mysql.compact') . "') AS date from syslog ORDER BY seq DESC LIMIT 20";

    echo('<div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              &nbsp;
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Syslog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">');

    foreach (dbFetchRows($sql) as $entry) {
        $entry = array_merge($entry, device_by_id_cache($entry['device_id']));

        unset($syslog_output);
        include("includes/html/print-syslog.inc.php");
        echo $syslog_output;
    }
    echo("</table>");
    echo("</div>");
    echo("</div>");
    echo("</div>");
    echo("</div>");
} else {
    if (Auth::user()->hasGlobalAdmin()) {
        $query = "SELECT *,DATE_FORMAT(datetime, '" . Config::get('dateformat.mysql.compact') . "') as humandate  FROM `eventlog` ORDER BY `datetime` DESC LIMIT 0,15";
    } else {
        $query = "SELECT *,DATE_FORMAT(datetime, '" . Config::get('dateformat.mysql.compact') . "') as humandate  FROM `eventlog` AS E, devices_perms AS P WHERE E.host =
    P.device_id AND P.user_id = " . Auth::id() . " ORDER BY `datetime` DESC LIMIT 0,15";
    }

    echo('<div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              &nbsp;
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Eventlog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">');

    foreach (dbFetchRows($query) as $entry) {
        include 'includes/html/print-event.inc.php';
    }

    echo("</table>");
    echo("</div>");
    echo("</div>");
    echo("</div>");
    echo("</div>");
}
?>
