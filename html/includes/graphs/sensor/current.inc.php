<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");

  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("current-" . $sensor['sensor_descr'] . ".rrd");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $sensor['sensor_descr_fixed'] = substr(str_pad($sensor['sensor_descr'], 28),0,28);

  $rrd_options .= " DEF:current=$rrd_filename:current:AVERAGE";
  $rrd_options .= " LINE1.5:current#cc0000:'" . $sensor['sensor_descr_fixed']."'";
  $rrd_options .= " GPRINT:current:LAST:%3.0lfA";
  $rrd_options .= " GPRINT:current:MAX:%3.0lfA\\\\l";

?>
