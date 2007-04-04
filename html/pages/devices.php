<?php

if($_GET['location']) { $where = "AND location = '$_GET[location]'"; }
if($_GET['location'] == "Unset") { $where = "AND location = ''"; }
if($_GET['type']) { $where = "AND type = '$_GET[type]'"; }
$sql = "select * from devices AS D, device_uptime AS U WHERE D.id = U.device_id $where ORDER BY `ignore`, `status`, `os`, `hostname`";
if($_GET['status'] == "alerted") { $sql = "select *, D.id as id from devices AS D, device_uptime AS U WHERE D.id = U.device_id " . str_replace("WHERE", "OR", $device_alert_sql) . " GROUP BY `id` ORDER BY `ignore`, `status`, `os`, `hostname`"; }

$device_query = mysql_query($sql);

echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

while($device = mysql_fetch_array($device_query)) {
  include("includes/hostbox.inc");
}

echo("</table>");

?>
