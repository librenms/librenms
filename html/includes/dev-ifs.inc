<?php

$hostname = gethostbyid($_GET[id]);

   echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
   $i = "1";
   $interface_query = mysql_query("select * from interfaces WHERE host = '$_GET[id]' ORDER BY 'ifIndex'");
   while($interface = mysql_fetch_array($interface_query)) {
     include("includes/print-interface.inc");
   }
   echo("</table></div>");

?>
