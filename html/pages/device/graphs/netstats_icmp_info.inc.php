<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/netstats-icmp.rrd"))
{
  $graph_title = "ICMP Informational Statistics";
  $graph_type = "device_icmp_informational";

  include("includes/print-device-graph.php");
}

?>