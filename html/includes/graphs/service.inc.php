<?php

$scale_min = "0";
$scale_max = "1";

include("common.inc.php");

  $iter = "1";

  $sql = "SELECT * FROM `services` AS S, `devices` AS D where S.`service_id` = '".mres($_GET['id'])."' AND S.device_id = D.device_id";
  $query = mysql_query($sql);
  $service = mysql_fetch_array($query);

  $rrd  = $config['rrd_dir'] . "/" . $service['hostname'] . "/" . safename("service-" . $service['service_type'] . "-" . $service['service_id'] . ".rrd");

  $rrd_options .= " COMMENT:'                                Cur    Max\\n'";
  $rrd_options .= " DEF:status=$rrd:status:AVERAGE";
  $rrd_options .= " CDEF:down=status,1,LT,status,UNKN,IF";
  $rrd_options .= " AREA:status#CCFFCC";
  $rrd_options .= " AREA:down#FFCCCC";
  $rrd_options .= " LINE1.5:status#009900:'" . $service['service_type'] . "'"; # Ugly hack :(
  $rrd_options .= " LINE1.5:down#cc0000";
  $rrd_options .= " GPRINT:status:LAST:%3.0lf";
  $rrd_options .= " GPRINT:status:MAX:%3.0lf\\\\l";




?>
