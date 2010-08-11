<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");

  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("current-" . $sensor['sensor_descr'] . ".rrd");

  $rrd_options .= " COMMENT:'                       Min     Last    Max\\n'";

  $sensor['sensor_descr_fixed'] = substr(str_pad($sensor['sensor_descr'], 18),0,18);

  $rrd_options .= " DEF:current=$rrd_filename:current:AVERAGE";
  $rrd_options .= " LINE1.5:current#cc0000:'" . $sensor['sensor_descr_fixed']."'";
  $rrd_options .= " GPRINT:current$current_id:MIN:%5.2lfA";
  $rrd_options .= " GPRINT:current:LAST:%5.2lfA";
  $rrd_options .= " GPRINT:current:MAX:%5.2lfA\\\\l";

  if(is_numeric($sensor['sensor_limit'])) $rrd_options .= " HRULE:".$sensor['sensor_limit']."#999999::dashes";
  if(is_numeric($sensor['sensor_limit_low'])) $rrd_options .= " HRULE:".$sensor['sensor_limit_low']."#999999::dashes";


?>
