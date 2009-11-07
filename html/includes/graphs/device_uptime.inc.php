<?php

$scale_min = "0";

include("common.inc.php");

if (is_file($config['rrd_dir'] . "/" . $hostname . "/hrSystem.rrd")) {
  $rrd_filename = $config['rrd_dir'] . "/" . $hostname . "/hrSystem.rrd";
} else {
  $rrd_filename = $config['rrd_dir'] . "/" . $hostname . "/uptime.rrd";
}

  $rrd_options .= " DEF:uptime=$rrd_filename:uptime:AVERAGE";
  $rrd_options .= " CDEF:cuptime=uptime,86400,/";
  $rrd_options .= " COMMENT:Days\ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
  $rrd_options .= " AREA:cuptime#EEEEEE:Uptime";
  $rrd_options .= " LINE1.25:cuptime#36393D:";
  $rrd_options .= " GPRINT:cuptime:LAST:%6.2lf\  GPRINT:cuptime:AVERAGE:%6.2lf\ ";
  $rrd_options .= " GPRINT:cuptime:MAX:%6.2lf\  GPRINT:cuptime:AVERAGE:%6.2lf\\\\n";

?>
