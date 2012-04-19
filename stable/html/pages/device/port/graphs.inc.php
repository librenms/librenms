<?php

if (file_exists($config['rrd_dir'] . "/" . $device['hostname'] . "/port-". $port['ifIndex'] . ".rrd"))
{
  $iid = $id;
  echo("<div class=graphhead>Interface Traffic</div>");
  $graph_type = "port_bits";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>Interface Packets</div>");
  $graph_type = "port_upkts";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>Interface Non Unicast</div>");
  $graph_type = "port_nupkts";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>Interface Errors</div>");
  $graph_type = "port_errors";

  include("includes/print-interface-graphs.inc.php");

  if (is_file($config['rrd_dir'] . "/" . $device['hostname'] . "/port-" . $port['ifIndex'] . "-dot3.rrd"))
  {
    echo("<div class=graphhead>Ethernet Errors</div>");
    $graph_type = "port_etherlike";

    include("includes/print-interface-graphs.inc.php");
  }
}

?>
