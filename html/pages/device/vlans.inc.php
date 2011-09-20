<?php

print_optionbar_start();

echo("<span style='font-weight: bold;'>VLANs</span> &#187; ");

echo("
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/'>Basic</a> | Graphs :
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/bits/'>Bits</a> |
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/pkts/'>Packets</a> |
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/nupkts/'>NU Packets</a> |
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/errors/'>Errors</a>
     ");

print_optionbar_end();

echo('<table border="0" cellspacing="0" cellpadding="5" width="100%">');

$i = "1";

foreach (dbFetchRows("SELECT * FROM `vlans` WHERE `device_id` = ? ORDER BY 'vlan_vlan'", array($device['device_id'])) as $vlan)
{
  include("includes/print-vlan.inc.php");
  $i++;
}

echo("</table>");

?>
