<?php

$overview = 1;

#$id = $_GET['id'];

#$device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE `device_id` = '$_GET[id]'"));

$ports['total'] = mysql_result(mysql_query("SELECT count(*) FROM ports  WHERE device_id = '" . $device['device_id'] . "'"),0);
$ports['up'] = mysql_result(mysql_query("SELECT count(*) FROM ports  WHERE device_id = '" . $device['device_id'] . "' AND ifOperStatus = 'up'"),0);
$ports['down'] = mysql_result(mysql_query("SELECT count(*) FROM ports WHERE device_id = '" . $device['device_id'] . "' AND ifOperStatus = 'down' AND ifAdminStatus = 'up'"),0);
$ports['disabled'] = mysql_result(mysql_query("SELECT count(*) FROM ports WHERE device_id = '" . $device['device_id'] . "' AND ifAdminStatus = 'down'"),0);

$services['total'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE device_id = '" . $device['device_id'] . "'"),0);
$services['up'] = mysql_result(mysql_query("SELECT count(service_id) FROM services  WHERE device_id = '" . $device['device_id'] . "' AND service_status = '1' AND service_ignore ='0'"),0);
$services['down'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE device_id = '" . $device['device_id'] . "' AND service_status = '0' AND service_ignore = '0'"),0);
$services['disabled'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE device_id = '" . $device['device_id'] . "' AND service_ignore = '1'"),0);

if($services['down']) { $services_colour = $warn_colour_a; } else { $services_colour = $list_colour_a; }
if($ports['down']) { $ports_colour = $warn_colour_a; } else { $ports_colour = $list_colour_a; }

echo("<div style='width: 50%; float: left;'>");

#if(file_exists("includes/dev-data-" . strtolower($device[os]) . ".inc.php")) {
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
#  echo("<p class=sectionhead>Device Data</p><div style='height: 5px;'></div>");
#  include("includes/dev-data-" . strtolower($device[os]) . ".inc.php");
  include("includes/dev-overview-data.inc.php");
  echo("</div>");
#}


include("overview/ports.inc.php");

if($services['total']) {

  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Services</p><div style='height: 5px;'></div>");

echo("
<table class=tablehead cellpadding=2 cellspacing=0 width=100%>
<tr bgcolor=$services_colour align=center><td></td>
<td width=25%><img src='images/16/cog.png' align=absmiddle> $services[total]</td>
<td width=25% class=green><img src='images/16/cog_go.png' align=absmiddle> $services[up]</td>
<td width=25% class=red><img src='images/16/cog_error.png' align=absmiddle> $services[down]</td>
<td width=25% class=grey><img src='images/16/cog_disable.png' align=absmiddle> $services[disabled]</td></tr>
</table>");

  echo("<div style='padding: 8px; font-size: 11px; font-weight: bold;'>");

  $sql = "SELECT * FROM services WHERE device_id = '" . $device['device_id'] . "' ORDER BY service_type";
  $query = mysql_query($sql);
  while($data = mysql_fetch_array($query)) {
    if ($data[service_status] == "0" && $data[service_ignore] == "1") { $status = "grey"; }
    if ($data[service_status] == "1" && $data[service_ignore] == "1") { $status = "green"; }
    if ($data[service_status] == "0" && $data[service_ignore] == "0") { $status = "red"; }
    if ($data[service_status] == "1" && $data[service_ignore] == "0") { $status = "blue"; }
    echo("$break<a class=$status>" . strtolower($data[service_type]) . "</a>");
    $break = ", ";
  }

  echo("</div>");

  echo("</div>");

}

echo("</div>");

echo("<div style='float:right; width: 50%;'>");


### Right Pane
include("overview/processors.inc.php");
include("overview/cemp.inc.php");
include("overview/cmp.inc.php");
include("overview/hrStorage.inc.php");
include("overview/temperatures.inc.php");
include("overview/fanspeeds.inc.php");
include("overview/voltages.inc.php");

echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
echo("<p class=sectionhead>Recent Events</p>");

$query = "SELECT *,DATE_FORMAT(datetime, '%d/%b/%y %T') as humandate  FROM `eventlog` WHERE `host` = '$_GET[id]' ORDER BY `datetime` DESC LIMIT 0,10";
$data = mysql_query($query);

echo("<table cellspacing=0 cellpadding=2 width=100%>");

while($entry = mysql_fetch_array($data)) {
  include("includes/print-event-short.inc");
}

echo("</table>");

echo("</div>");
?>
