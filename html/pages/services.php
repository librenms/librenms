<?php

if($_GET['status'] == '0') { $where = " AND service_status = '0'"; } else { unset ($where); }

echo("<div style='margin: 5px;'><table cellpadding=7 border=0 cellspacing=0 width=100%>");
//echo("<tr class=interface-desc bgcolor='#e5e5e5'><td>Device</td><td>Service</td><td>Status</td><td>Changed</td><td>Checked</td><td>Message</td></tr>");

if ($_SESSION['userlevel'] >= '5') {
  $host_sql = "SELECT * FROM devices AS D, services AS S WHERE D.device_id = S.device_id GROUP BY D.hostname ORDER BY D.hostname";
} else {
  $host_sql = "SELECT * FROM devices AS D, services AS S, devices_perms AS P WHERE D.device_id = S.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' $where GROUP BY D.hostname ORDER BY D.hostname";
}
  $host_query = mysql_query($host_sql);
  while($host_data = mysql_fetch_array($host_query)) {
    $device_id = $host_data['device_id'];
    $device_hostname = $host_data['hostname'];
    $service_query = mysql_query("SELECT * FROM `services` WHERE `device_id` = '" . $host_data['device_id'] . "' $where");
    while($service = mysql_fetch_array($service_query)) {
       include("includes/print-service.inc");
       $samehost = 1;
    }
    unset ($samehost);
  }	

  echo("</table></div>");

?>
