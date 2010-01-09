<?php

echo("
<div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 10px;'>
  <b class='rounded'>
  <b class='rounded1'></b>
  <b class='rounded2'></b>
  <b class='rounded3'></b>
  <b class='rounded4'></b>
  <b class='rounded5'></b></b>
  <div class='roundedfg' style='padding: 0px 5px;'>
  <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:20px;'>
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/'>Basic</a> | Graphs :
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/bits/'>Bits</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/pkts/'>Packets</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/nupkts/'>NU Packets</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/errors/'>Errors</a>
</div>
</div>
  <b class='rounded'>
  <b class='rounded5'></b>
  <b class='rounded4'></b>
  <b class='rounded3'></b>
  <b class='rounded2'></b>
  <b class='rounded1'></b></b>
</div>
 ");


   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "0";
   $vrf_query = mysql_query("select * from vrfs WHERE device_id = '".$_GET['id']."' ORDER BY 'vrf_name'");
   while($vrf = mysql_fetch_array($vrf_query)) {
     include("includes/print-vrf.inc");
     $i++;
   }
   echo("</table></div>");

?>
