<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/netstats-tcp.rrd"))
{
  $graph_title = "TCP Statistics";
  $graph_type = "device_tcp";

  include("includes/print-device-graph.php");
}

?>