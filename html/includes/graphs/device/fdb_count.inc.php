<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");

  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/fdb_count.rrd";

  $rrd_options .= " DEF:value=$rrd_filename:value:AVERAGE";
  $rrd_options .= " CDEF:cvalue=value,86400,/";
  $rrd_options .= " COMMENT:'MACs      Current  Minimum  Maximum  Average\\n'";
  $rrd_options .= " AREA:cvalue#EEEEEE:value";
  $rrd_options .= " LINE1.25:cvalue#36393D:";
  $rrd_options .= " GPRINT:cvalue:LAST:%6.2lf\  GPRINT:cvalue:AVERAGE:%6.2lf\ ";
  $rrd_options .= " GPRINT:cvalue:MAX:%6.2lf\  GPRINT:cvalue:AVERAGE:%6.2lf\\\\n";

?>
