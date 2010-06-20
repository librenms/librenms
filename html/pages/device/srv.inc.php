<?php

if(mysql_result(mysql_query("select count(service_id) from services WHERE device_id = '".$device['device_id']."'"), 0) > '0') {

   echo("<div style='margin: 5px;'><table cellpadding=7 border=0 cellspacing=0 width=100%>");
   $i = "1";
   $service_query = mysql_query("select * from services WHERE device_id = '".$device['device_id']."' ORDER BY service_type");
   while($service = mysql_fetch_array($service_query)) {

	include("includes/print-service.inc");
   }
   echo("</table></div>");

} else {

   echo("No Services");

}


?>

