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
?>
<script src="js/leaflet.js"></script>
<script src="js/leaflet.markercluster-src.js"></script>
<script src="js/leaflet.awesome-markers.min.js"></script>
<div id="leaflet-map"></div>
<script>
<?php

$map_init = "[" . $config['leaflet']['default_lat'] . ", " . $config['leaflet']['default_lng'] . "], " . $config['leaflet']['default_zoom'];

?>
var map = L.map('leaflet-map').setView(<?php echo $map_init; ?>);

L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

var markers = L.markerClusterGroup();
var redMarker = L.AwesomeMarkers.icon({
    icon: 'server',
    markerColor: 'red', prefix: 'fa', iconColor: 'white'
  });
var greenMarker = L.AwesomeMarkers.icon({
    icon: 'server',
    markerColor: 'green', prefix: 'fa', iconColor: 'white'
  });

<?php
foreach (dbFetchRows("SELECT `hostname`,`lat`,`lng`,`status` FROM `devices` WHERE `lat` IS NOT NULL AND `lng` IS NOT NULL AND `disabled`=0 AND `ignore`=0 ORDER BY `status` ASC, `hostname`") as $map_devices) {

    $icon = 'greenMarker';
    if ($map_devices['status'] == 0) {
        $icon = 'redMarker';
    }

    echo "var title = '" . $map_devices['hostname'] . "';
         var marker = L.marker(new L.LatLng(".$map_devices['lat'].", ".$map_devices['lng']."), {title: title, icon: $icon});
         marker.bindPopup(title);
         markers.addLayer(marker);\n";
}
?>

map.addLayer(markers);
</script>
<?php
} else {

if (isset($config['mapael']['default_map']) && is_file($config['html_dir'].'/js/'.$config['mapael']['default_map'])) {
    $default_map = $config['mapael']['default_map'];
} else {
    $default_map = 'maps/world_countries.js';
}
$map_tmp = preg_split("/\//",$default_map);
$map_name = $map_tmp[count($map_tmp)-1];
$map_name = str_replace('.js','',$map_name);

if (isset($config['mapael']['map_width']) && is_numeric($config['mapael']['map_width'])) {
    $map_width = $config['mapael']['map_width'];
} else {
    $map_width = '800';
}

if (isset($config['mapael']['default_zoom'])) {
    $default_zoom = $config['mapael']['default_zoom'];
} else {
    $default_zoom = 10;
}

if (isset($config['mapael']['default_lat']) && isset($config['mapael']['default_lng'])) {
    $init_zoom = "init: {
                            latitude: " . $config['mapael']['default_lat'] . ",
                            longitude: " . $config['mapael']['default_lng'] . ",
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
foreach (dbFetchRows("SELECT `lat`,`lng`,`status`,COUNT(`status`) AS `total` FROM `devices` WHERE `lat` IS NOT NULL AND `lng` IS NOT NULL AND `disabled`=0 AND `ignore`=0 GROUP BY `status`,`lat`,`lng` ORDER BY `status` ASC, `hostname`") as $map_devices) {
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
include_once("includes/object-cache.inc.php");
echo '<div class="container-fluid">
	<div class="row">
		<div class="col-md-4">';
			include_once("includes/front/boxes.inc.php");
echo '		</div>
                <div class="col-md-2">
                </div>
		<div class="col-md-4">';
			include_once("includes/device-summary-vert.inc.php");
echo '		</div>
	</div>
	<div class="row">
		<div class="col-md-12">';
			$device['device_id'] = '-1';
			require_once('includes/print-alerts.php');
			unset($device['device_id']);
echo '		</div>
	</div>
</div>';

//From default.php - This code is not part of above license.
if ($config['enable_syslog']) {
$sql = "SELECT *, DATE_FORMAT(timestamp, '".$config['dateformat']['mysql']['compact']."') AS date from syslog ORDER BY seq DESC LIMIT 20";
$query = mysql_query($sql);
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

  foreach (dbFetchRows($sql) as $entry)
  {
    $entry = array_merge($entry, device_by_id_cache($entry['device_id']));

    include("includes/print-syslog.inc.php");
  }
  echo("</table>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
  echo("</div>");

} else {

  if ($_SESSION['userlevel'] == '10')
  {
    $query = "SELECT *,DATE_FORMAT(datetime, '".$config['dateformat']['mysql']['compact']."') as humandate  FROM `eventlog` ORDER BY `datetime` DESC LIMIT 0,15";
  } else {
    $query = "SELECT *,DATE_FORMAT(datetime, '".$config['dateformat']['mysql']['compact']."') as humandate  FROM `eventlog` AS E, devices_perms AS P WHERE E.host =
    P.device_id AND P.user_id = " . $_SESSION['user_id'] . " ORDER BY `datetime` DESC LIMIT 0,15";
  }

  $data = mysql_query($query);

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

  foreach (dbFetchRows($query) as $entry)
  {
    include("includes/print-event.inc.php");
  }

  echo("</table>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
}
?>
