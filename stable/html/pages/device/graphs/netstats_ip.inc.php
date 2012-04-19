<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/netstats-ip.rrd"))
{
  $graph_title = "IP Statistics";
  $graph_type = "device_ip";

  include("includes/print-device-graph.php");

  $graph_title = "IP Fragmented Statistics";
  $graph_type = "device_ip_fragmented";

  include("includes/print-device-graph.php");
}

?>