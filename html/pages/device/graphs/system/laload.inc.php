<?php

if(is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/ucd_load.rrd")) {
  $graph_title = "System Load";
  $graph_type = "device_laload";         
  include ("includes/print-device-graph.php");
}

?>
