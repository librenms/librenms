<?php

$scale_min = "0";

include("common.inc.php");

  $rrd_options .= " COMMENT:'                         Last     Max\\n'";

  $fanspeed = mysql_fetch_array(mysql_query("SELECT * FROM fanspeed where fan_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $fanspeed['device_id'] . "'"),0);

  $fanspeed['fan_descr_fixed'] = substr(str_pad($fanspeed['fan_descr'], 20),0,20);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("fan-" . $fanspeed['fan_descr'] . ".rrd");

  $rrd_options .= " DEF:fan=$rrd_filename:fan:AVERAGE";
  $rrd_options .= " AREA:fan#FFFF99";
  $rrd_options .= " LINE1.5:fan#cc0000:'" . str_replace(':','\:',str_replace('\*','*',quotemeta($fanspeed['fan_descr_fixed'])))."'"; # Ugly hack :(
  $rrd_options .= " GPRINT:fan:LAST:%3.0lfrpm";
  $rrd_options .= " GPRINT:fan:MAX:%3.0lfrpm\\\\l";

?>
