<?php

  include("common.inc.php");

  $rrd_filename = $config['rrd_dir'] . "/" . $hostname . "/netstats-icmp.rrd";

  $rrd_options .= " DEF:icmpInMsgs=$rrd_filename:icmpInMsgs:AVERAGE";
  $rrd_options .= " DEF:icmpOutMsgs=$rrd_filename:icmpOutMsgs:AVERAGE";
  $rrd_options .= " DEF:icmpInErrors=$rrd_filename:icmpInErrors:AVERAGE";
  $rrd_options .= " DEF:icmpOutErrors=$rrd_filename:icmpOutErrors:AVERAGE";
  $rrd_options .= " DEF:icmpInEchos=$rrd_filename:icmpInEchos:AVERAGE";
  $rrd_options .= " DEF:icmpOutEchos=$rrd_filename:icmpOutEchos:AVERAGE";
  $rrd_options .= " DEF:icmpInEchoReps=$rrd_filename:icmpInEchoReps:AVERAGE";
  $rrd_options .= " DEF:icmpOutEchoReps=$rrd_filename:icmpOutEchoReps:AVERAGE";
  $rrd_options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ \ Average\ \ \ Maximum\\\\n";
  $rrd_options .= " LINE1.25:icmpInMsgs#00cc00:InMsgs ";
  $rrd_options .= " GPRINT:icmpInMsgs:LAST:\ \ \ \ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpInMsgs:AVERAGE:\ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpInMsgs:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:icmpOutMsgs#006600:OutMsgs    ";
  $rrd_options .= " GPRINT:icmpOutMsgs:LAST:\ \ \ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpOutMsgs:AVERAGE:\ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpOutMsgs:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:icmpInErrors#cc0000:InErrors   ";
  $rrd_options .= " GPRINT:icmpInErrors:LAST:\ \ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpInErrors:AVERAGE:\ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpInErrors:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:icmpOutErrors#660000:OutErrors  ";
  $rrd_options .= " GPRINT:icmpOutErrors:LAST:\ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpOutErrors:AVERAGE:\ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpOutErrors:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:icmpInEchos#0066cc:InEchos    ";
  $rrd_options .= " GPRINT:icmpInEchos:LAST:\ \ \ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpInEchos:AVERAGE:\ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpInEchos:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:icmpOutEchos#003399:OutEchos   ";
  $rrd_options .= " GPRINT:icmpOutEchos:LAST:\ \ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpOutEchos:AVERAGE:\ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpOutEchos:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:icmpInEchoReps#cc00cc:InEchoReps ";
  $rrd_options .= " GPRINT:icmpInEchoReps:LAST:\ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpInEchoReps:AVERAGE:\ \ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpInEchoReps:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:icmpOutEchoReps#990099:OutEchoReps";
  $rrd_options .= " GPRINT:icmpOutEchoReps:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:icmpOutEchoReps:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:icmpOutEchoReps:MAX:\ %6.2lf%s\\\\n";

?>
