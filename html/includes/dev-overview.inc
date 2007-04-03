<?php

$overview = 1;

$id = $_GET[id];

$device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE id = '$_GET[id]'"));

$interfaces['total'] = mysql_result(mysql_query("SELECT count(id) FROM interfaces  WHERE host = '$id'"),0);
$interfaces['up'] = mysql_result(mysql_query("SELECT count(id) FROM interfaces  WHERE host = '$id' AND up = 'up'"),0);
$interfaces['down'] = mysql_result(mysql_query("SELECT count(id) FROM interfaces WHERE host = '$id' AND up = 'down' AND up_admin = 'up'"),0);
$interfaces['disabled'] = mysql_result(mysql_query("SELECT count(id) FROM interfaces WHERE host = '$id' AND up_admin = 'down'"),0);

$services['total'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_host = '$id'"),0);
$services['up'] = mysql_result(mysql_query("SELECT count(service_id) FROM services  WHERE service_host = '$id' AND service_status = '1' AND service_ignore ='0'"),0);
$services['down'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_host = '$id' AND service_status = '0' AND service_ignore = '0'"),0);
$services['disabled'] = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_host = '$id' AND service_ignore = '1'"),0);

if($services['down']) { $services_colour = $warn_colour_a; } else { $services_colour = $list_colour_a; }
if($interfaces['down']) { $interfaces_colour = $warn_colour_a; } else { $interfaces_colour = $list_colour_a; }

echo("
<table width=100% cellspacing=0 cellpadding=0>
  <tr><td width=50% valign=top>");


if(file_exists("includes/dev-data-" . strtolower($device[os]) . ".inc")) {
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Device Data</p><div style='height: 5px;'></div>");
  include("includes/dev-data-" . strtolower($device[os]) . ".inc");
  echo("</div>");
}


echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");

$query = "SELECT *,DATE_FORMAT(datetime, '%d/%b/%y %T') as humandate  FROM `eventlog` WHERE `host` = '$_GET[id]' ORDER BY `datetime` DESC LIMIT 0,10";
$data = mysql_query($query);

echo("<p class=sectionhead>Recent Events</p><div style='height: 5px;'></div>
<table cellspacing=0 cellpadding=2 width=100%>");

while($entry = mysql_fetch_array($data)) {

include("includes/print-event.inc");

}
echo("</table></div>");

echo("</td><td width=50% valign=top>");

if($interfaces['total']) {

  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Interfaces</p><div style='height: 5px;'></div>");

echo("
<table class=tablehead cellpadding=2 cellspacing=0 width=100%>
<tr bgcolor=$interfaces_colour align=center><td></td>
<td width=25%><img src='images/16/connect.png' align=absmiddle> $interfaces[total]</td>
<td width=25% class=green><img src='images/16/if-connect.png' align=absmiddle> $interfaces[up]</td>
<td width=25% class=red><img src='images/16/if-disconnect.png' align=absmiddle> $interfaces[down]</td>
<td width=25% class=grey><img src='images/16/if-disable.png' align=absmiddle> $interfaces[disabled]</td></tr>
</table>");

  echo("<div style='margin: 8px; font-size: 11px; font-weight: bold;'>");

  $sql = "SELECT * FROM interfaces WHERE host = '$id'";
  $query = mysql_query($sql);
  while($data = mysql_fetch_array($query)) {
    $up_admin = $data['up_admin'];
    $up = $data['up'];
    if ($up_admin == "down") { $status = "grey"; }
    if ($up_admin == "up" && $up == "down") { $status = "red"; }
    if ($up_admin == "up" && $up == "up") { $status = "blue"; }
    echo("$ifbreak<a class=$status>" . generateiflink($data, makeshortif(strtolower($data['if']))) . "</a>");
    $ifbreak = ", ";
  }
  unset($break);
  echo("</div>");

  echo("</div>");

}

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

  $sql = "SELECT * FROM services WHERE service_host = '$id' ORDER BY service_type";
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

}



echo("</td></tr></table>");

?>
