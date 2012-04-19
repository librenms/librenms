<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/ucd_mem.rrd"))
{
  $graph_title = "Memory Utilisation";
  $graph_type = "device_memory";

  include("includes/print-device-graph.php");
}

?>