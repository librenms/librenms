<?php

if($_SESSION['userlevel'] >= '5') {

echo("
<div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 10px;'>
  <b class='rounded'>
  <b class='rounded1'><b></b></b>
  <b class='rounded2'><b></b></b>
  <b class='rounded3'></b>
  <b class='rounded4'></b>
  <b class='rounded5'></b></b>
  <div class='roundedfg' style='padding: 0px 5px;'>
  <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:20px;'>
<a href='".$config['base_url']. "/vrfs/'>Basic</a> | Graphs :
<a href='".$config['base_url']. "/vrfs/bits/'>Bits</a> |
<a href='".$config['base_url']. "/vrfs/pkts/'>Packets</a> |
<a href='".$config['base_url']. "/vrfs/nupkts/'>NU Packets</a> |
<a href='".$config['base_url']. "/vrfs/errors/'>Errors</a>
</div>
</div>
  <b class='rounded'>
  <b class='rounded5'></b>
  <b class='rounded4'></b>
  <b class='rounded3'></b>
  <b class='rounded2'><b></b></b>
  <b class='rounded1'><b></b></b></b>
</div>
");


   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "1";
   $vrf_query = mysql_query("SELECT * FROM `vrfs` GROUP BY `mplsVpnVrfRouteDistinguisher`");
   while($vrf = mysql_fetch_array($vrf_query)) {

     if(!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
     echo("<tr valign=top bgcolor='$bg_colour'>");
     echo("<td width=240><a class=list-large href='vrf/".$vrf['mplsVpnVrfRouteDistinguisher']."/'>" . $vrf['vrf_name'] . "</a><br /><span class=box-desc>" . $vrf['mplsVpnVrfDescription'] . "</span></td>");
     echo("<td width=100 class=box-desc>" . $vrf['mplsVpnVrfRouteDistinguisher'] . "</td>");
     #echo("<td width=200 class=box-desc>" . $vrf['mplsVpnVrfDescription'] . "</td>");
     echo("<td><table border=0 cellspacing=0 cellpadding=5 width=100%>");
     $devices = mysql_query("SELECT * FROM `vrfs` AS V, `devices` AS D WHERE `mplsVpnVrfRouteDistinguisher` = '".$vrf['mplsVpnVrfRouteDistinguisher']."' AND D.device_id = V.device_id");
     $x=1;
     while($device = mysql_fetch_array($devices)) {

       if(!is_integer($i/2)) {
         if(!is_integer($x/2)) { $dev_colour = $list_colour_a_a; } else { $dev_colour = $list_colour_a_b; }
       } else {
         if(!is_integer($x/2)) { $dev_colour = $list_colour_b_b; } else { $dev_colour = $list_colour_b_a; }
       }
       echo("<tr bgcolor='$dev_colour'><td width=150>".generatedevicelink($device, shorthost($device['hostname'])));
	if($device['vrf_name'] != $vrf['vrf_name']) { echo("<a href='#' onmouseover=\" return overlib('Expected Name : ".$vrf['vrf_name']."<br />Configured : ".$device['vrf_name']."', CAPTION, '<span class=list-large>VRF Inconsistency</span>' ,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\"> <img align=absmiddle src=images/16/exclamation.png></a>"); }
       echo("</td><td>");
       $interfaces = mysql_query("SELECT * FROM `interfaces` WHERE `ifVrf` = '".$device['vrf_id']."' and device_id = '".$device['device_id']."'");
       unset($seperator);
       while($port = mysql_fetch_array($interfaces)) {
         if($_GET['opta']) {
           $graph_type = $_GET['opta'];
           include("includes/print-port-thumbs.inc.php");
         } else {
  	   $port = array_merge ($device, $port);
           echo($seperator.generateiflink($port,makeshortif($port['ifDescr']))); 
	   $seperator = ", ";         
         }
       }
       echo("</td></tr>");
       $x++;
     } // End While

     echo("</table></td>");


     $i++;
   }
   echo("</table></div>");

} else {

include("includes/error-no-perm.inc.php");

} ## End Permission if

?>

