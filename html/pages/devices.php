<?php

if($_GET['ignore']) { mysql_query("UPDATE devices SET `ignore` = '1' WHERE `id` = '$_GET[ignore]'"); }
if($_GET['unignore']) { mysql_query("UPDATE devices SET `ignore` = '0' WHERE `id` = '$_GET[unignore]'"); }

if($_GET['location']) { $where = "WHERE location = '$_GET[location]'"; }
if($_GET['location'] == "Unset") { $where = "WHERE location = ''"; }
if($_GET['type']) { $where = "WHERE type = '$_GET[type]'"; }
if($_GET['status'] == "alerted") { $where = $device_alert_sql; }

$sql = "select * from devices $where ORDER BY `ignore`, STATUS, os, hostname";
$device_query = mysql_query($sql);

echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

//echo("<tr class=interface-desc bgcolor=#e5e5e5 style='font-weight:bold;'>
//<td></td>
//<td>Hostname - Description</td>
//<td>Operating System - Version</td>
//<td>Hardware - Features</td>
//<td>Uptime - Location</td>
//<td></td>
//</tr>");

while($device = mysql_fetch_array($device_query)) {

  include("includes/hostbox.inc");

}

echo("</table>");

?>
