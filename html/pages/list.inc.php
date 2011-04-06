<?php

if ($_GET['location']) { $where = "AND location = '$_GET[location]'"; }
if ($_GET['location'] == "Unset") { $where = "AND location = ''"; }
if ($_GET['type']) { $where = "AND type = '$_GET[type]'"; }

$sql = "select *, U.device_uptime as uptime from devices AS D, device_uptime AS U WHERE D.id = U.device_id $where ORDER BY `ignore`, `status`, `os`, `hostname`";
$device_query = mysql_query($sql);

echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");

echo("<tr class=interface-desc bgcolor=#e5e5e5 style='font-weight:bold;'>
<td></td>
<td>Hostname - Description</td>
<td>Operating System - Version</td>
<td>Hardware - Features</td>
<td>Uptime - Location</td>
<td></td>
</tr>");

while ($device = mysql_fetch_assoc($device_query))
{
  include("includes/hostbox.inc.php");
}

echo("</table>");

?>