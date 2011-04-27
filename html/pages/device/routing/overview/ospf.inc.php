<?php

## We are assuming there will only ever be one instance, because OSPF-MIB is retarded.

$instance = mysql_fetch_assoc(mysql_query("SELECT * FROM `ospf_instances` WHERE `device_id` = '".$device['device_id']."'"));

$area_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `ospf_areas` WHERE `device_id` = '".$device['device_id']."'"),0);
$port_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `ospf_ports` WHERE `device_id` = '".$device['device_id']."'"),0);

echo("
<table width=100%>
  <tr>
    <th>Router ID</th>
    <th>Area Count</th>
    <th>Port Count</th>
    <th>Neighbour Count</th>
  </tr>
  <tr>
    <td>".$instance['ospfRouterId']."</td>
    <td>".$area_count."</td>
    <td>".$port_count."</td>
  </tr>
</table>
");

?>
