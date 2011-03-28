<?php

print_optionbar_start();

echo("
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/'>Basic</a> | Graphs :
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/bits/'>Bits</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/upkts/'>Packets</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/nupkts/'>NU Packets</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/errors/'>Errors</a>
 ");
print_optionbar_end();

echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
$i = "0";
$vrf_query = mysql_query("select * from vrfs WHERE device_id = '".$device['device_id']."' ORDER BY 'vrf_name'");
while ($vrf = mysql_fetch_array($vrf_query))
{
  include("includes/print-vrf.inc.php");
  $i++;
}

echo("</table></div>");

?>