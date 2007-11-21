<?php

if($_GET['location']) { $where = "AND location = '$_GET[location]'"; }
if($_GET['location'] == "Unset") { $where = "AND location = ''"; }
if($_GET['type']) { $where = "AND type = '$_GET[type]'"; }

$sql = "select * from devices WHERE 1 $where ORDER BY `ignore`, `status`, `os`, `hostname`";
if($_GET['status'] == "alerted") { 
  $sql = "select * from devices " . $device_alert_sql . " GROUP BY `device_id` ORDER BY `ignore`, `status`, `os`, `hostname`";  
}

$device_query = mysql_query($sql);

echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

while($device = mysql_fetch_array($device_query)) {
  if( devicepermitted($device['device_id']) || $_SESSION['userlevel'] >= '5' ) {
    $device['uptime'] = @mysql_result(mysql_query("SELECT `attrib_value` FROM `devices_attribs` WHERE `device_id` = '" . $device['device_id'] ."' AND `attrib_type` = 'uptime'"), 0);
    include("includes/hostbox.inc");
  }
}

echo("</table>");

?>
