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
<script>
</script>
<script src='js/jquery-jvectormap.min.js'></script>
<script src='js/jquery-jvectormap-world-mill-en.js'></script>
<script src='js/jquery-jvectormap-de-merc-en.js'></script>
<?php
foreach (dbFetchRows("SELECT `hostname`,`lat`,`lng`,COUNT(`status`) AS `status` FROM `devices` WHERE `lat` IS NOT NULL AND `lng` IS NOT NULL AND `disabled`=0 AND `ignore`=0 GROUP BY `status`,`lat`,`lng` ORDER BY `hostname`") as $loc) {
    $data .= "{latLng: [". $loc['lat'] . ", " . $loc['lng'] . "], name: '".$loc['status'] ."'},\n";
}
?>
    <div id="map" style="width: 720px; height: 600px"></div>
    <script>
    $('#map').vectorMap({
    map: 'world_mill_en',
    markers: [ <?php echo $data; ?>],
    series: {
            markers: [{
              attribute: 'r',
              scale: [5, 15],
              values: [10,100,200]
            }]
          }
    });
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
