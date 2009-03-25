<?php

$hostname = gethostbyid($device['device_id']);

   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "1";
   $interface_query = mysql_query("select * from interfaces WHERE device_id = '$_GET[id]' AND deleted = '0' ORDER BY `ifDescr` ASC");
   while($interface = mysql_fetch_array($interface_query)) {
     include("includes/print-interface.inc");
   }
   echo("</table></div>");

?>
