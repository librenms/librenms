<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/netstats-ip_forward.rrd"))
{
  $graph_title = "IP Forward statistics";
  $graph_type = "device_ip_forward";

  include("includes/print-device-graph.php");

}

