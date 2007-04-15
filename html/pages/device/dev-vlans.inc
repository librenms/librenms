<?php

   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "1";
   $vlan_query = mysql_query("select * from vlans WHERE device_id = '$_GET[id]' ORDER BY 'vlan_vlan'");
   while($vlan = mysql_fetch_array($vlan_query)) {
     include("includes/print-vlan.inc");
     $i++;
   }
   echo("</table></div>");

?>
