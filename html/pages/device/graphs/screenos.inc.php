<?php

if ($device['os'] == "screenos" && is_file($config['rrd_dir'] . "/" . $device['hostname'] ."/screenos-sessions.rrd"))
{
  $graph_title = "Firewall Sessions";
  $graph_type = "screenos_sessions";

  include("includes/print-device-graph.php");
}

?>