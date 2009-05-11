<?php

   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "1";
   $vrf_query = mysql_query("SELECT * FROM `vrfs` GROUP BY `mplsVpnVrfRouteDistinguisher`");
   while($vrf = mysql_fetch_array($vrf_query)) {

     if(!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

     echo("<tr valign=top bgcolor='$bg_colour'>");

     echo("<td width=200 class=list-large>" . $vrf['vrf_name'] . "</td>");
     echo("<td width=100 class=box-desc>" . $vrf['mplsVpnVrfRouteDistinguisher'] . "</td>");

     echo("<td width=200 class=box-desc>" . $vrf['mplsVpnVrfDescription'] . "</td>");

     echo("<td><table border=0 cellspacing=0 cellpadding=5 width=100%>");

     $devices = mysql_query("SELECT * FROM `vrfs` AS V, `devices` AS D WHERE `mplsVpnVrfRouteDistinguisher` = '".$vrf['mplsVpnVrfRouteDistinguisher']."' AND D.device_id = V.device_id");
     $x=1;
     while($device = mysql_fetch_array($devices)) {
       if(!is_integer($i/2)) {
         if(!is_integer($x/2)) { $dev_colour = $list_colour_a_a; } else { $dev_colour = $list_colour_a_b; }
       } else {
         if(!is_integer($x/2)) { $dev_colour = $list_colour_b_b; } else { $dev_colour = $list_colour_b_a; }
       }
       echo("<tr bgcolor='$dev_colour'><td width=150>".generatedevicelink($device) . "</td><td>");
       $interfaces = mysql_query("SELECT * FROM `interfaces` WHERE `ifVrf` = '".$device['vrf_id']."' and device_id = '".$device['device_id']."'");
       unset($seperator);
       while($interface = mysql_fetch_array($interfaces)) {
         echo($seperator.generateiflink($interface,makeshortif($interface['ifDescr']))); 
	 $seperator = ", ";         
       }
       echo("</td></tr>");
       $x++;
     }

     echo("</table></td>");


     $i++;
   }
   echo("</table></div>");

?>

