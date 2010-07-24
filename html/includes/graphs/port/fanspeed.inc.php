<?php

$scale_min = "0";

include("common.inc.php");

  $rrd_options .= " COMMENT:'                         Last     Max\\n'";

  $fanspeed = mysql_fetch_array(mysql_query("SELECT * FROM sensors WHERE sensor_class='fanspeed' AND sensor_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $fanspeed['device_id'] . "'"),0);

  $fanspeed['sensor_descr_fixed'] = substr(str_pad($fanspeed['sensor_descr'], 20),0,20);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("fan-" . $fanspeed['sensor_descr'] . ".rrd");

  $rrd_options .= " DEF:fan=$rrd_filename:fan:AVERAGE";
  $rrd_options .= " LINE1.5:fan#cc0000:'" . str_replace(':','\:',str_replace('\*','*',quotemeta($fanspeed['sensor_descr_fixed'])))."'"; # Ugly hack :(
  $rrd_options .= " GPRINT:fan:LAST:%3.0lfrpm";
  $rrd_options .= " GPRINT:fan:MAX:%3.0lfrpm\\\\l";

?>
