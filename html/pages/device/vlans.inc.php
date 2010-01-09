<?php

echo("
<div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 0px;'>
  <b class='rounded'>
  <b class='rounded1'></b>
  <b class='rounded2'></b>
  <b class='rounded3'></b>
  <b class='rounded4'></b>
  <b class='rounded5'></b></b>
  <div class='roundedfg' style='padding: 0px 5px;'>
    <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:20px;'>
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/'>Basic</a> | Graphs : 
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/bits/'>Bits</a> |
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/pkts/'>Packets</a> | 
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/nupkts/'>NU Packets</a> | 
      <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/errors/'>Errors</a> 
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

   echo("<table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "1";
   $vlan_query = mysql_query("select * from vlans WHERE device_id = '".$_GET['id']."' ORDER BY 'vlan_vlan'");
   while($vlan = mysql_fetch_array($vlan_query)) {
     include("includes/print-vlan.inc");
     $i++;
   }
   echo("</table>");

?>
