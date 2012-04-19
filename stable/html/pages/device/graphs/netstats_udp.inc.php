<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/netstats-udp.rrd"))
{
  $graph_title = "UDP Statistics";
  $graph_type = "device_udp";

  include("includes/print-device-graph.php");
}

?>