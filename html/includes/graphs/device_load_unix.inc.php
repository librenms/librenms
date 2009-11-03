<?php

include("common.inc.php");

$rrd_filename = $config['rrd_dir'] . "/" . $_GET['device'] . "/uptime.rrd";

  $options .= " DEF:1min=$rrd_filename:1min:AVERAGE";
  $options .= " DEF:5min=$rrd_filename:5min:AVERAGE";
  $options .= " DEF:15min=$rrd_filename:15min:AVERAGE";
  $options .= " CDEF:a=1min,100,/";
  $options .= " CDEF:b=5min,100,/";
  $options .= " CDEF:c=15min,100,/";
  $options .= " CDEF:cdefd=a,b,c,+,+";
  $options .= " COMMENT:Load\ Average\ \ Current\ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " AREA:a#ffeeaa:1\ Min:";
  $options .= " LINE1:a#c5aa00:";
  $options .= " GPRINT:a:LAST:\ \ \ \ %7.2lf";
  $options .= " GPRINT:a:AVERAGE:\ \ %7.2lf";
  $options .= " GPRINT:a:MAX:\ \ %7.2lf\\\\n";
  $options .= " LINE1.25:b#ea8f00:5\ Min:";
  $options .= " GPRINT:b:LAST:\ \ \ \ %7.2lf";
  $options .= " GPRINT:b:AVERAGE:\ \ %7.2lf";
  $options .= " GPRINT:b:MAX:\ \ %7.2lf\\\\n";
  $options .= " LINE1.25:c#cc0000:15\ Min";
  $options .= " GPRINT:c:LAST:\ \ \ %7.2lf";
  $options .= " GPRINT:c:AVERAGE:\ \ %7.2lf";
  $options .= " GPRINT:c:MAX:\ \ %7.2lf\\\\n";


?>
