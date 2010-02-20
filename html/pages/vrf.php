<?php

echo("
<div style='width: 100%; text-align: right; padding-bottom: 10px; clear: both; display:block; height:20px;'>
<a href='".$config['base_url']."/vrf/" . $_GET['id'] . "/'>Basic</a> |
<a href='".$config['base_url']."/vrf/" . $_GET['id'] . "/details/'>Details</a> | Graphs:
<a href='".$config['base_url']."/vrf/" . $_GET['id'] . "/graphs/bits/'>Bits</a> |
<a href='".$config['base_url']."/vrf/" . $_GET['id'] . "/graphs/pkts/'>Packets</a> |
<a href='".$config['base_url']."/vrf/" . $_GET['id'] . "/graphs/nupkts/'>NU Packets</a> |
<a href='".$config['base_url']."/vrf/" . $_GET['id'] . "/graphs/errors/'>Errors</a>
</div> ");

if($_GET['opta'] == graphs ) {
  if($_GET['optb']) {
    $graph_type = $_GET['optb'];
  } else {
    $graph_type = "bits";
  }
  $dographs = 1;
}

if($_GET['opta'] == "details" ) {
  $port_details = 1;
}

   echo("<div style='background: $list_colour_b; padding: 10px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $vrf_query = mysql_query("SELECT * FROM `vrfs` WHERE mplsVpnVrfRouteDistinguisher = '".$_GET['id']."'");
   $vrf = mysql_fetch_array($vrf_query);
   echo("<tr valign=top bgcolor='$bg_colour'>");
   echo("<td width=200 class=list-large><a href='vrf/".$vrf['mplsVpnVrfRouteDistinguisher']."/'>" . $vrf['vrf_name'] . "</a></td>");
   echo("<td width=100 class=box-desc>" . $vrf['mplsVpnVrfRouteDistinguisher'] . "</td>");
   echo("<td width=200 class=box-desc>" . $vrf['mplsVpnVrfDescription'] . "</td>");
   echo("</table></div>");


   $devices = mysql_query("SELECT * FROM `vrfs` AS V, `devices` AS D WHERE `mplsVpnVrfRouteDistinguisher` = '".$vrf['mplsVpnVrfRouteDistinguisher']."' AND D.device_id = V.device_id");
   $x=1;
   while($device = mysql_fetch_array($devices)) {
     $hostname = $device['hostname'];
     #if(!is_integer($x/2)) { $device_colour = $list_colour_a; } else { $device_colour = $list_colour_b; }
     echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");
     include("includes/device-header.inc");
     echo("</table>");
     $ports = mysql_query("SELECT * FROM `ports` WHERE `ifVrf` = '".$device['vrf_id']."' and device_id = '".$device['device_id']."'");
     unset($seperator);
     echo("<table cellspacing=0 cellpadding=7>");
     $i=1;
     while($interface = mysql_fetch_array($ports)) {
       if(!is_integer($x/2)) {
         if(is_integer($i/2)) { $int_colour = $list_colour_a_a; } else { $int_colour = $list_colour_a_b; }
       } else {
         if(is_integer($i/2)) { $int_colour = $list_colour_b_b; } else { $int_colour = $list_colour_b_a; }
       }
       include("includes/print-interface.inc.php");
       $i++;
     }
     $x++;
     echo("</table>");
     echo("<div style='height: 10px;'></div>");
   }

?>

