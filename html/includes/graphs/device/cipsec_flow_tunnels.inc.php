<?php

  include("includes/graphs/common.inc.php");

  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cipsec_flow.rrd";

  $rrd_options .= " DEF:Tunnels=$rrd_filename:Tunnels:AVERAGE";

  $rrd_options .= " COMMENT:'Tunnels           Current   Average   Maximum\\n'";
  $rrd_options .= " LINE1.25:Tunnels#660000:'Active Tunnels'";
  $rrd_options .= " GPRINT:Tunnels:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:Tunnels:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:Tunnels:MAX:\ %6.2lf%s\\\\n";


?>
