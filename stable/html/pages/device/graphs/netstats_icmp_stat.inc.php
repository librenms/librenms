<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/netstats-icmp.rrd"))
{
  $graph_title = "ICMP Statistics";
  $graph_type = "device_icmp";

  include("includes/print-device-graph.php");
}

?>
