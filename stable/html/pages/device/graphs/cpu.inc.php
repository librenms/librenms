<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/ucd_cpu.rrd"))
{
  $graph_title = "Processor Utilisation";
  $graph_type = "device_cpu";

  include("includes/print-device-graph.php");
}

?>
