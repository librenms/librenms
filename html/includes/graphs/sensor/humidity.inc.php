<?php

$scale_min = "25";
$scale_max = "40";

include("includes/graphs/common.inc.php");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $rrd_filename  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("humidity-" . $sensor['sensor_descr'] . ".rrd");


  $sensor['sensor_descr_fixed'] = substr(str_pad($sensor['sensor_descr'], 28),0,28);

  $rrd_options .= " DEF:humidity=$rrd_filename:humidity:AVERAGE";
  $rrd_options .= " DEF:humidity_max=$rrd_filename:humidity:MAX";
  $rrd_options .= " DEF:humidity_min=$rrd_filename:humidity:MIN";
  $rrd_options .= " CDEF:humiditywarm=humidity_max,".$sensor['sensor_limit'].",GT,humidity,UNKN,IF";
  $rrd_options .= " CDEF:humiditycold=humidity_min,20,LT,humidity,UNKN,IF";
  $rrd_options .= " AREA:humidity_max#c5c5c5";
  $rrd_options .= " AREA:humidity_min#ffffffff";

#  $rrd_options .= " AREA:humidity#bbd392";
#  $rrd_options .= " AREA:humiditywarm#FFCCCC";
#  $rrd_options .= " AREA:humiditycold#CCCCFF";
  $rrd_options .= " LINE1:humidity#cc0000:'" . str_replace(':','\:',str_replace('\*','*',quotemeta($sensor['sensor_descr_fixed'])))."'"; # Ugly hack :(
  $rrd_options .= " LINE1:humiditywarm#660000";
  $rrd_options .= " GPRINT:humidity:LAST:%3.0lf%%";
  $rrd_options .= " GPRINT:humidity:MAX:%3.0lf%%\\\\l";

  if(is_numeric($sensor['sensor_limit'])) $rrd_options .= " HRULE:".$sensor['sensor_limit']."#999999::dashes";
  if(is_numeric($sensor['sensor_limit_low'])) $rrd_options .= " HRULE:".$sensor['sensor_limit_low']."#999999::dashes";

?>
