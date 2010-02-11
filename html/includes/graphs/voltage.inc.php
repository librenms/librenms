<?php

$scale_min = "0";

include("common.inc.php");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $voltage = mysql_fetch_array(mysql_query("SELECT * FROM voltage where volt_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $voltage['volt_host'] . "'"),0);

  $voltage['volt_descr_fixed'] = str_pad($voltage['volt_descr'], 28);
  $voltage['volt_descr_fixed'] = substr($voltage['volt_descr_fixed'],0,28);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("volt-" . $voltage['volt_descr'] . ".rrd");

  $rrd_options .= " DEF:volt=$rrd_filename:volt:AVERAGE";
  $rrd_options .= " CDEF:voltwarm=volt,".$voltage['volt_limit'].",GT,volt,UNKN,IF";
  $rrd_options .= " AREA:volt#FFFF99";
  $rrd_options .= " AREA:voltwarm#FF9999";
  $rrd_options .= " LINE1.5:volt#cc0000:'" . $voltage['volt_descr_fixed']."'";
  $rrd_options .= " LINE1.5:voltwarm#660000";
  $rrd_options .= " GPRINT:volt:LAST:%3.0lfC";
  $rrd_options .= " GPRINT:volt:MAX:%3.0lfC\\\\l";

?>
