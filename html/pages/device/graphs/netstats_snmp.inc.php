<?php

if (is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/netstats-snmp.rrd"))
{
  $graph_title = "SNMP Packets Statistics";
  $graph_type = "device_snmp_packets";

  include("includes/print-device-graph.php");

  $graph_title = "SNMP Message Type Statistics";
  $graph_type = "device_snmp_statistics";

  include("includes/print-device-graph.php");
}

?>