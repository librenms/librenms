<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $frequency = mysql_fetch_array(mysql_query("SELECT * FROM sensors where sensor_class='freq' AND sensor_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $frequency['device_id'] . "'"),0);

  $frequency['sensor_descr_fixed'] = substr(str_pad($frequency['sensor_descr'], 28),0,28);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("freq-" . $frequency['sensor_descr'] . ".rrd");

  $rrd_options .= " DEF:freq=$rrd_filename:freq:AVERAGE";
  $rrd_options .= " LINE1.5:freq#cc0000:'" . $frequency['sensor_descr_fixed']."'";
  $rrd_options .= " GPRINT:freq:LAST:%3.0lfHz";
  $rrd_options .= " GPRINT:freq:MAX:%3.0lfHz\\\\l";

?>
