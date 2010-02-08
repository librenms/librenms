<?php
if(mysql_result(mysql_query("SELECT count(*) FROM temperature WHERE temp_host = '" . $device['device_id'] . "'"),0)) {
  $graph_title = "Temperatures";
  $graph_type = "device_temperatures";             include ("includes/print-device-graph.php");
  echo("<br />");
}
?>
