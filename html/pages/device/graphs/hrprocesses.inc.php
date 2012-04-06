<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/hrSystem.rrd"))
{
  $graph_title = "Running Processes";
  $graph_type = "device_hrprocesses";

  include("includes/print-device-graph.php");
}

?>