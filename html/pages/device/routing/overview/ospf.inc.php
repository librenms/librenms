<?php

$sql   = "SELECT * FROM `ospf_instances` WHERE `device_id` = '".$device['device_id']."'";
$query = mysql_query($sql);

$i_i = "1";

echo('<table width=100%>');

#### Loop Instances

while($instance = mysql_fetch_assoc($query))
{

  if (!is_integer($i_i/2)) { $instance_bg = $list_colour_a; } else { $instance_bg = $list_colour_b; }

  $area_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `ospf_areas` WHERE `device_id` = '".$device['device_id']."'"),0);
  $port_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `ospf_ports` WHERE `device_id` = '".$device['device_id']."'"),0);

  $query = "SELECT * FROM ipv4_addresses AS A, ports AS I WHERE ";
  $query .= "(A.ipv4_address = '".$peer['bgpPeerIdentifier']."' AND I.interface_id = A.interface_id)";
  $query .= " AND I.device_id = '".$device['device_id']."'";
  $ipv4_host = mysql_fetch_assoc(mysql_query($query));

  if($instance['ospfAdminStat'] == "enabled") { $enabled = '<span style="color: #00aa00">enabled</span>'; } else { $enabled = '<span style="color: #aaaaaa">disabled</span>'; }
  if($instance['ospfAreaBdrRtrStatus'] == "true") { $abr = '<span style="color: #00aa00">yes</span>'; } else { $abr = '<span style="color: #aaaaaa">no</span>'; }
  if($instance['ospfASBdrRtrStatus'] == "true") { $asbr = '<span style="color: #00aa00">yes</span>'; } else { $asbr = '<span style="color: #aaaaaa">no</span>'; }

  echo('<tr><th>Router Id</th><th>Status</th><th>ABR</th><th>ASBR</th><th>Areas</th><th>Ports</th><th>Neighbours</th></tr>');
  echo('<tr bgcolor="'.$instance_bg.'">');
  echo('  <td class="list-large">'.$instance['ospfRouterId'] . '</td>');
  echo('  <td>' . $enabled . '</td>');
  echo('  <td>' . $abr . '</td>');
  echo('  <td>' . $asbr . '</td>');
  echo('  <td>' . $area_count . '</td>');
  echo('  <td>' . $port_count . '</td>');
  echo('  <td>' . ($neighbour_count+0) . '</td>');
  echo('</tr>');


  $i_i++;

} ### End loop instances

echo('</table>');

?>
