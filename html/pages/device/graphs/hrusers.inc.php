<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/hrSystem.rrd"))
{
  $graph_title = "Users Logged On";
  $graph_type = "device_hrusers";

  include("includes/print-device-graph.php");
}

?>