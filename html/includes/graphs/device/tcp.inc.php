<?php

  include("includes/graphs/common.inc.php");
  $device = device_by_id_cache($id);

  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-tcp.rrd";

  $rrd_options .= " DEF:tcpActiveOpens=$rrd_filename:tcpActiveOpens:AVERAGE";
  $rrd_options .= " DEF:tcpPassiveOpens=$rrd_filename:tcpPassiveOpens:AVERAGE";
  $rrd_options .= " DEF:tcpAttemptFails=$rrd_filename:tcpAttemptFails:AVERAGE";
  $rrd_options .= " DEF:tcpEstabResets=$rrd_filename:tcpEstabResets:AVERAGE";
  $rrd_options .= " DEF:tcpInSegs=$rrd_filename:tcpInSegs:AVERAGE";
  $rrd_options .= " DEF:tcpOutSegs=$rrd_filename:tcpOutSegs:AVERAGE";
  $rrd_options .= " DEF:tcpRetransSegs=$rrd_filename:tcpRetransSegs:AVERAGE";
  $rrd_options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ \ Average\ \ \ Maximum\\\\n";
  $rrd_options .= " LINE1.25:tcpActiveOpens#00cc00:ActiveOpens\ ";
  $rrd_options .= " GPRINT:tcpActiveOpens:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:tcpActiveOpens:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:tcpActiveOpens:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:tcpPassiveOpens#006600:PassiveOpens";
  $rrd_options .= " GPRINT:tcpPassiveOpens:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:tcpPassiveOpens:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:tcpPassiveOpens:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:tcpAttemptFails#cc0000:AttemptFails";
  $rrd_options .= " GPRINT:tcpAttemptFails:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:tcpAttemptFails:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:tcpAttemptFails:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:tcpEstabResets#660000:EstabResets\ ";
  $rrd_options .= " GPRINT:tcpEstabResets:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:tcpEstabResets:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:tcpEstabResets:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:tcpInSegs#0066cc:InSegs\ \ \ \ \ \ ";
  $rrd_options .= " GPRINT:tcpInSegs:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:tcpInSegs:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:tcpInSegs:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:tcpOutSegs#003399:OutSegs\ \ \ \ \ ";
  $rrd_options .= " GPRINT:tcpOutSegs:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:tcpOutSegs:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:tcpOutSegs:MAX:\ %6.2lf%s\\\\n";
  $rrd_options .= " LINE1.25:tcpRetransSegs#cc00cc:RetransSegs\ ";
  $rrd_options .= " GPRINT:tcpRetransSegs:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:tcpRetransSegs:AVERAGE:\ %6.2lf%s";
  $rrd_options .= " GPRINT:tcpRetransSegs:MAX:\ %6.2lf%s\\\\n";

?>
