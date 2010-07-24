<?php

$scale_min = "25";
$scale_max = "40";

include("includes/graphs/common.inc.php");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $humidity = mysql_fetch_array(mysql_query("SELECT * FROM sensors WHERE sensor_class='humidity' AND sensor_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $humidity['device_id'] . "'"),0);

  $humidity['sensor_descr_fixed'] = substr(str_pad($humidity['sensor_descr'], 28),0,28);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("humidity-" . $humidity['sensor_descr'] . ".rrd");


  $rrd_options .= " DEF:humidity=$rrd_filename:humidity:AVERAGE";
  $rrd_options .= " DEF:humidity_max=$rrd_filename:humidity:MAX";
  $rrd_options .= " DEF:humidity_min=$rrd_filename:humidity:MIN";
  $rrd_options .= " CDEF:humiditywarm=humidity_max,".$humidity['sensor_limit'].",GT,humidity,UNKN,IF";
  $rrd_options .= " CDEF:humiditycold=humidity_min,20,LT,humidity,UNKN,IF";
  $rrd_options .= " AREA:humidity_max#c5c5c5";
  $rrd_options .= " AREA:humidity_min#ffffffff";



#  $rrd_options .= " AREA:humidity#bbd392";
#  $rrd_options .= " AREA:humiditywarm#FFCCCC";
#  $rrd_options .= " AREA:humiditycold#CCCCFF";
  $rrd_options .= " LINE1:humidity#cc0000:'" . str_replace(':','\:',str_replace('\*','*',quotemeta($humidity['sensor_descr_fixed'])))."'"; # Ugly hack :(
  $rrd_options .= " LINE1:humiditywarm#660000";
  $rrd_options .= " GPRINT:humidity:LAST:%3.0lf%%";
  $rrd_options .= " GPRINT:humidity:MAX:%3.0lf%%\\\\l";

?>
