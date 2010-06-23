<?php

$scale_min = "25";
$scale_max = "40";

include("common.inc.php");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $temperature = mysql_fetch_array(mysql_query("SELECT * FROM temperature where temp_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $temperature['device_id'] . "'"),0);

  $temperature['temp_descr_fixed'] = substr(str_pad($temperature['temp_descr'], 28),0,28);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("temp-" . $temperature['temp_descr'] . ".rrd");


  $rrd_options .= " DEF:temp=$rrd_filename:temp:AVERAGE";
  $rrd_options .= " DEF:temp_max=$rrd_filename:temp:MAX";
  $rrd_options .= " DEF:temp_min=$rrd_filename:temp:MIN";
  $rrd_options .= " CDEF:tempwarm=temp_max,".$temperature['temp_limit'].",GT,temp,UNKN,IF";
  $rrd_options .= " CDEF:tempcold=temp_min,20,LT,temp,UNKN,IF";
  $rrd_options .= " AREA:temp_max#c5c5c5";
  $rrd_options .= " AREA:temp_min#ffffffff";



#  $rrd_options .= " AREA:temp#bbd392";
#  $rrd_options .= " AREA:tempwarm#FFCCCC";
#  $rrd_options .= " AREA:tempcold#CCCCFF";
  $rrd_options .= " LINE1:temp#cc0000:'" . str_replace(':','\:',str_replace('\*','*',quotemeta($temperature['temp_descr_fixed'])))."'"; # Ugly hack :(
  $rrd_options .= " LINE1:tempwarm#660000";
  $rrd_options .= " GPRINT:temp:LAST:%3.0lfC";
  $rrd_options .= " GPRINT:temp:MAX:%3.0lfC\\\\l";

?>
