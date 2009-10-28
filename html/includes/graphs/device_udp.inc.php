<?php

  include("common.inc.php");

  $rrd_filename = $config['rrd_dir'] . "/" . $hostname . "/netstats-udp.rrd";

  $rrd_options .= " DEF:udpInDatagrams=$rrd_filename:udpInDatagrams:AVERAGE";
  $rrd_options .= " DEF:udpOutDatagrams=$rrd_filename:udpOutDatagrams:AVERAGE";
  $rrd_options .= " DEF:udpInErrors=$rrd_filename:udpInErrors:AVERAGE";
  $rrd_options .= " DEF:udpNoPorts=$rrd_filename:udpNoPorts:AVERAGE";
  $rrd_options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ \ Average\ \ \ Maximum\\\\n";
  $rrd_options .= " LINE1.25:udpInDatagrams#00cc00:InDatagrams\ ";
  $rrd_options .= " GPRINT:udpInDatagrams:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:udpInDatagrams:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:udpInDatagrams:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:udpOutDatagrams#006600:OutDatagrams";
  $rrd_options .= " GPRINT:udpOutDatagrams:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:udpOutDatagrams:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:udpOutDatagrams:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:udpInErrors#cc0000:InErrors\ \ \ \ ";
  $rrd_options .= " GPRINT:udpInErrors:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:udpInErrors:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:udpInErrors:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:udpNoPorts#660000:NoPorts\ \ \ \ \ ";
  $rrd_options .= " GPRINT:udpNoPorts:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:udpNoPorts:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:udpNoPorts:MAX:\ %6.2lf%s\\\\n";


?>
