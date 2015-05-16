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

?>
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type='text/javascript'>
	google.load('visualization', '1', {'packages': ['geochart']});
	google.setOnLoadCallback(drawRegionsMap);
	function drawRegionsMap() {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Site');
		data.addColumn('number', 'Status');
		data.addColumn('number', 'Size');
		data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
		data.addRows([
<?php
$locations = array();
foreach (getlocations() as $location) {
	$devices = array();
	$devices_down = array();
	$devices_up = array();
	$count = 0;
	$down  = 0;
	foreach (dbFetchRows("SELECT devices.device_id,devices.hostname,devices.status FROM devices LEFT JOIN devices_attribs ON devices.device_id = devices_attribs.device_id WHERE ( devices.location = ? || ( devices_attribs.attrib_type = 'override_sysLocation_string' && devices_attribs.attrib_value = ? ) ) && devices.disabled = 0 && devices.ignore = 0 GROUP BY devices.hostname", array($location,$location)) as $device) {
		if( $config['frontpage_custom']['globe'] == 'devices' || empty($config['frontpage_custom']['globe']) ) {
			$devices[] = $device['hostname'];
			$count++;
			if( $device['status'] == "0" ) {
				$down++;
				$devices_down[] = $device['hostname']." DOWN";
			} else {
				$devices_up[] = $device;
			}
		} elseif( $config['frontpage_custom']['globe'] == 'ports' ) {
			foreach( dbFetchRows("SELECT ifName,ifOperStatus,ifAdminStatus FROM ports WHERE ports.device_id = ? && ports.ignore = 0 && ports.disabled = 0 && ports.deleted = 0",array($device['device_id'])) as $port ) {
				$count++;
				if( $port['ifOperStatus'] == 'down' && $port['ifAdminStatus'] == 'up' ) {
					$down++;
					$devices_down[] = $device['hostname']."/".$port['ifName']." DOWN";
				} else {
					$devices_up[] = $port;
				}
			}
		}
	}
	$pdown = ($down / $count)*100;
	if( $config['frontpage_custom']['globe'] == 'devices' || empty($config['frontpage_custom']['globe']) ) {
		$devices_down = array_merge(array(count($devices_up). " Devices OK"), $devices_down);
	} elseif( $config['frontpage_custom']['globe'] == 'ports' ) {
		$devices_down = array_merge(array(count($devices_up). " Ports OK"), $devices_down);
	}
	$locations[] = "			['".$location."', ".$pdown.", ".$count.", '".implode(",<br/> ", $devices_down)."']";
}
echo implode(",\n", $locations);
?>

		]);
		var options = {
			region: 'world',
			resolution: 'countries',
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
</script>
<?php
include_once("includes/object-cache.inc.php");
echo '<div class="container-fluid">
	<div class="row">
		<div class="col-md-8">
			<div id="chart_div"></div>
		</div>
		<div class="col-md-4">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-4">';
						include_once("includes/device-summary-vert.inc.php");
echo '					</div>
				</div>
				<div class="row">
					<div class="col-md-4">';
						include_once("includes/front/boxes.inc.php");
echo '					</div>
				</div>
			</div>
		</div>
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
$sql = "SELECT *, DATE_FORMAT(timestamp, '%D %b %T') AS date from syslog ORDER BY seq DESC LIMIT 20";
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
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %T') as humandate  FROM `eventlog` ORDER BY `datetime` DESC LIMIT 0,15";
  } else {
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %T') as humandate  FROM `eventlog` AS E, devices_perms AS P WHERE E.host =
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
