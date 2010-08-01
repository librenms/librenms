<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");

  $rrd_options .= " COMMENT:'                         Last     Max\\n'";

  $rrd_filename  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("fan-" . $sensor['sensor_descr'] . ".rrd");

  $sensor['sensor_descr_fixed'] = substr(str_pad($sensor['sensor_descr'], 20),0,20);

  $rrd_options .= " DEF:fan=$rrd_filename:fan:AVERAGE";
  $rrd_options .= " LINE1.5:fan#cc0000:'" . str_replace(':','\:',str_replace('\*','*',quotemeta($sensor['sensor_descr_fixed'])))."'"; # Ugly hack :(
  $rrd_options .= " GPRINT:fan:LAST:%3.0lfrpm";
  $rrd_options .= " GPRINT:fan:MAX:%3.0lfrpm\\\\l";

?>
