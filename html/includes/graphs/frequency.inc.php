<?php

$scale_min = "0";

include("common.inc.php");

  $rrd_options .= " COMMENT:'                                 Last   Max\\n'";

  $frequency = mysql_fetch_array(mysql_query("SELECT * FROM frequency where freq_id = '".mres($_GET['id'])."'"));

  $hostname = mysql_result(mysql_query("SELECT hostname FROM devices WHERE device_id = '" . $frequency['device_id'] . "'"),0);

  $frequency['freq_descr_fixed'] = substr(str_pad($frequency['freq_descr'], 28),0,28);

  $rrd_filename  = $config['rrd_dir'] . "/".$hostname."/" . safename("freq-" . $frequency['freq_descr'] . ".rrd");

  $rrd_options .= " DEF:freq=$rrd_filename:freq:AVERAGE";
  $rrd_options .= " AREA:freq#FFFF99";
  $rrd_options .= " LINE1.5:freq#cc0000:'" . $frequency['freq_descr_fixed']."'";
  $rrd_options .= " GPRINT:freq:LAST:%3.0lfHz";
  $rrd_options .= " GPRINT:freq:MAX:%3.0lfHz\\\\l";

?>
