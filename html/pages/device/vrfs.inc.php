<?php

echo("
<div style='background-color: ".$list_colour_b."; margin: auto; margin-bottom: 5px; text-align: left; padding: 7px; padding-left: 11px; clear: both; display:block; height:20px;'>
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/'>Basic</a> | Graphs :
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/bits/'>Bits</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/pkts/'>Packets</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/nupkts/'>NU Packets</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/errors/'>Errors</a>
</div> ");


   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "1";
   $vrf_query = mysql_query("select * from vrfs WHERE device_id = '".$_GET['id']."' ORDER BY 'vrf_name'");
   while($vrf = mysql_fetch_array($vrf_query)) {
     include("includes/print-vrf.inc");
     $i++;
   }
   echo("</table></div>");

?>
