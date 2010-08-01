<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");

  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("freq-" . $sensor['sensor_descr'] . ".rrd");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $sensor['sensor_descr_fixed'] = substr(str_pad($sensor['sensor_descr'], 28),0,28);

  $rrd_options .= " DEF:freq=$rrd_filename:freq:AVERAGE";
  $rrd_options .= " LINE1.5:freq#cc0000:'" . $sensor['sensor_descr_fixed']."'";
  $rrd_options .= " GPRINT:freq:LAST:%3.0lfHz";
  $rrd_options .= " GPRINT:freq:MAX:%3.0lfHz\\\\l";

?>
