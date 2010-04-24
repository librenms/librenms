<?php

$scale_min = "0";
$scale_max = "1";

include("common.inc.php");

  $iter = "1";

  $sql = "SELECT * FROM `services` AS S, `devices` AS D where S.`service_id` = '".mres($_GET['id'])."' AND S.device_id = D.device_id";
  $query = mysql_query($sql);
  $service = mysql_fetch_array($query);

  $service_text = substr(str_pad($service['service_type'], 28),0,28);

  $rrd  = $config['rrd_dir'] . "/" . $service['hostname'] . "/" . safename("service-" . $service['service_type'] . "-" . $service['service_id'] . ".rrd");

  $rrd_options .= " COMMENT:'                                Cur    Avail\\n'";
  $rrd_options .= " DEF:status=$rrd:status:AVERAGE";
  $rrd_options .= " CDEF:percent=status,100,*";
  $rrd_options .= " CDEF:down=status,1,LT,status,UNKN,IF";
  $rrd_options .= " CDEF:percentdown=down,100,*";
  $rrd_options .= " AREA:percent#CCFFCC";
  $rrd_options .= " AREA:percentdown#FFCCCC";
  $rrd_options .= " LINE1.5:percent#009900:'" . $service_text . "'"; # Ugly hack :(
  $rrd_options .= " LINE1.5:percentdown#cc0000";
  $rrd_options .= " GPRINT:status:LAST:%3.0lf";
  $rrd_options .= " GPRINT:percent:AVERAGE:%3.5lf%%\\\\l";




?>
