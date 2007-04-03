<?php

if($_GET['status'] == '0') { $where = " AND service_status = '0'"; }

echo("<div style='margin: 5px;'><table cellpadding=7 border=0 cellspacing=0 width=100%>");
//echo("<tr class=interface-desc bgcolor='#e5e5e5'><td>Device</td><td>Service</td><td>Status</td><td>Changed</td><td>Checked</td><td>Message</td></tr>");


  $host_sql = "SELECT D.id, D.hostname FROM devices AS D, services AS S WHERE D.id = S.service_host GROUP BY D.hostname ORDER BY D.hostname";
  $host_query = mysql_query($host_sql);
  while($host_data = mysql_fetch_array($host_query)) {
    $device_id = $host_data[id];
    $device_hostname = $host_data[hostname];
    $service_query = mysql_query("SELECT * FROM `services` WHERE `service_host` = '$host_data[id]' $where");
    while($service = mysql_fetch_array($service_query)) {
       include("includes/print-service.inc");
       $samehost = 1;
    }
    unset ($samehost);
  }	

  echo("</table></div>");

?>
