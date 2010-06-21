<?php

$scale_min = "0";

include("common.inc.php");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $current = mysql_fetch_array(mysql_query("SELECT * FROM current where current_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $current['device_id'] . "'"),0);

  $current['current_descr_fixed'] = substr(str_pad($current['current_descr'], 28),0,28);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("current-" . $current['current_descr'] . ".rrd");

  $rrd_options .= " DEF:current=$rrd_filename:current:AVERAGE";
  $rrd_options .= " LINE1.5:current#cc0000:'" . $current['current_descr_fixed']."'";
  $rrd_options .= " GPRINT:current:LAST:%3.0lfA";
  $rrd_options .= " GPRINT:current:MAX:%3.0lfA\\\\l";

?>
