<?php

echo("<div class=graphhead>CPU Usage</div>");
$graph_type = "cpu";              include ("includes/print-device-graph.php");
echo("<br />");
echo("<div class=graphhead>Memory Usage</div>");
$graph_type = "mem";              include ("includes/print-device-graph.php");
echo("<br />");
if(mysql_result(mysql_query("SELECT count(*) FROM temperature WHERE temp_host = '" . $device['device_id'] . "'"),0)) {
  echo("<div class=graphhead>Temperatures</div>");
  $graph_type = "dev_temp";             include ("includes/print-device-graph.php");
  echo("<br />");
}

include("netstats.inc.php");
include("uptime.inc.php");

?>
