<?php

print_optionbar_start();

echo("<div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:20px;'>
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/'>Basic</a> | Graphs :
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/bits/'>Bits</a> |
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/pkts/'>Packets</a> |
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/nupkts/'>NU Packets</a> |
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/errors/'>Errors</a> ");

print_optionbar_end();

echo('<table border="0" cellspacing="0" cellpadding="5" width="100%">');

$i = "1";
$vlan_query = mysql_query("select * from vlans WHERE device_id = '".$device['device_id']."' ORDER BY 'vlan_vlan'");

while ($vlan = mysql_fetch_assoc($vlan_query))
{
  include("includes/print-vlan.inc.php");
  $i++;
}

echo("</table>");

?>
