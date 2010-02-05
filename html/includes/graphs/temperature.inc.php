<?php

$scale_min = "0";

include("common.inc.php");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $sql = mysql_query("SELECT * FROM temperature where temp_id = '$temp'");
  $temperature = mysql_fetch_array(mysql_query("SELECT * FROM temperature where temp_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $temperature['temp_host'] . "'"),0);

  $temperature['temp_descr_fixed'] = str_pad($temperature['temp_descr'], 28);
  $temperature['temp_descr_fixed'] = substr($temperature['temp_descr_fixed'],0,28);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("temp-" . $temperature['temp_descr'] . ".rrd");

  $rrd_options .= " DEF:temp=$rrd_filename:temp:AVERAGE";
  $rrd_options .= " CDEF:tempwarm=temp,".$temperature['temp_limit'].",GT,temp,UNKN,IF";
  $rrd_options .= " CDEF:tempcold=temp,20,LT,temp,UNKN,IF";
  $rrd_options .= " AREA:temp#FFFF99";
  $rrd_options .= " AREA:tempwarm#FF9999";
  $rrd_options .= " AREA:tempcold#CCCCFF";
  $rrd_options .= " LINE1.5:temp#cc0000:'" . str_replace(':','\:',str_replace('\*','*',quotemeta($temperature['temp_descr_fixed']))."'"); # Ugly hack :(
  $rrd_options .= " LINE1.5:tempwarm#660000";
  $rrd_options .= " GPRINT:temp:LAST:%3.0lfC";
  $rrd_options .= " GPRINT:temp:MAX:%3.0lfC\\\\l";

?>
