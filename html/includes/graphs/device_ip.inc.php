<?php

  include("common.inc.php");
  $device = device_by_id_cache($id);

  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-ip.rrd";

  $rrd_options .= " DEF:ipForwDatagrams=$rrd_filename:ipForwDatagrams:AVERAGE";
  $rrd_options .= " DEF:ipInDelivers=$rrd_filename:ipInDelivers:AVERAGE";
  $rrd_options .= " DEF:ipInReceives=$rrd_filename:ipInReceives:AVERAGE";
  $rrd_options .= " DEF:ipOutRequests=$rrd_filename:ipOutRequests:AVERAGE";
  $rrd_options .= " DEF:ipInDiscards=$rrd_filename:ipInDiscards:AVERAGE";
  $rrd_options .= " DEF:ipOutDiscards=$rrd_filename:ipOutDiscards:AVERAGE";
  $rrd_options .= " DEF:ipOutNoRoutes=$rrd_filename:ipInDiscards:AVERAGE";
  $rrd_options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ Average\ \ \ Maximum\\\\n";
  $rrd_options .= " LINE1.25:ipForwDatagrams#cc0000:ForwDgrams\ ";
  $rrd_options .= " GPRINT:ipForwDatagrams:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:ipForwDatagrams:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:ipForwDatagrams:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:ipInDelivers#00cc00:InDelivers\ ";
  $rrd_options .= " GPRINT:ipInDelivers:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:ipInDelivers:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:ipInDelivers:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:ipInReceives#006600:InReceives\ ";
  $rrd_options .= " GPRINT:ipInReceives:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:ipInReceives:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:ipInReceives:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:ipOutRequests#0000cc:OutRequests";
  $rrd_options .= " GPRINT:ipOutRequests:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:ipOutRequests:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:ipOutRequests:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:ipInDiscards#cccc00:InDiscards\ ";
  $rrd_options .= " GPRINT:ipInDiscards:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:ipInDiscards:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:ipInDiscards:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:ipOutDiscards#330033:OutDiscards";
  $rrd_options .= " GPRINT:ipOutDiscards:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:ipOutDiscards:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:ipOutDiscards:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:ipOutNoRoutes#660000:OutNoRoutes";
  $rrd_options .= " GPRINT:ipOutNoRoutes:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:ipOutNoRoutes:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:ipOutNoRoutes:MAX:\ %6.2lf%s\\\\n";

?>
