<?php

   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "1";
   $vrf_query = mysql_query("select * from vrfs WHERE device_id = '".$_GET['id']."' ORDER BY 'vrf_name'");
   while($vrf = mysql_fetch_array($vrf_query)) {
     include("includes/print-vrf.inc");
     $i++;
   }
   echo("</table></div>");

?>
