<?php

if($_SESSION['userlevel'] >= '5') {
print_optionbar_start();
echo("
<a href='".$config['base_url']. "/vrfs/'>Basic</a> | Graphs :
<a href='".$config['base_url']. "/vrfs/bits/'>Bits</a> |
<a href='".$config['base_url']. "/vrfs/upkts/'>Packets</a> |
<a href='".$config['base_url']. "/vrfs/nupkts/'>NU Packets</a> |
<a href='".$config['base_url']. "/vrfs/errors/'>Errors</a>
");
print_optionbar_end();


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
       echo("<tr bgcolor='$dev_colour'><td width=150>".generate_device_link($device, shorthost($device['hostname'])));
	if($device['vrf_name'] != $vrf['vrf_name']) { echo("<a href='#' onmouseover=\" return overlib('Expected Name : ".$vrf['vrf_name']."<br />Configured : ".$device['vrf_name']."', CAPTION, '<span class=list-large>VRF Inconsistency</span>' ,FGCOLOR,'#e5e5e5', BGCOLOR, '#c0c0c0', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#050505');\" onmouseout=\"return nd();\"> <img align=absmiddle src=images/16/exclamation.png></a>"); }
       echo("</td><td>");
       $ports = mysql_query("SELECT * FROM `ports` WHERE `ifVrf` = '".$device['vrf_id']."' and device_id = '".$device['device_id']."'");
       unset($seperator);

       while($port = mysql_fetch_array($ports)) {
         $port = array_merge ($device, $port);
         if($_GET['opta']) {
           $port['width'] = "130";
           $port['height'] = "30";
           $port['from'] = $day;
           $port['to'] = $now;
           $port['bg'] = "#".$bg;
           $port['graph_type'] = "port_".$_GET['opta'];
           echo("<div style='display: block; padding: 3px; margin: 3px; min-width: 135px; max-width:135px; min-height:75px; max-height:75px;
             text-align: center; float: left; background-color: ".$list_colour_b_b.";'>
             <div style='font-weight: bold;'>".makeshortif($port['ifDescr'])."</div>");
           generate_port_thumbnail($port);
           echo("<div style='font-size: 9px;'>".truncate(short_port_descr($port['ifAlias']), 22, '')."</div>
            </div>");
         } else {
           echo($seperator.generate_port_link($port,makeshortif($port['ifDescr']))); 
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

