<?php

if($_GET['location']) { $where = "WHERE location = '$_GET[location]'"; }
if($_GET['location'] == "Unset") { $where = "WHERE location = ''"; }
if($_GET['type']) { $where = "WHERE type = '$_GET[type]'"; }

$sql = "select * from devices $where ORDER BY `ignore`, `status`, `os`, `hostname`";

if($_GET['status'] == "alerted") { $sql = "select * from devices " . $device_alert_sql . " GROUP BY `device_id` ORDER BY `ignore`, `status`, `os`, `hostname`"; }

$device_query = mysql_query($sql);

echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

while($device = mysql_fetch_array($device_query)) {
  $device[uptime] = @mysql_result(mysql_query("SELECT device_uptime FROM device_uptime WHERE device_id = '" . $device[id] ."'" ), 0);
  include("includes/hostbox.inc");
}

echo("</table>");

?>
