<?php

  include("common.inc.php");

  $rrd_filename_4 = $config['rrd_dir'] . "/" . $hostname . "/ipSystemStats-ipv4.rrd";
  $rrd_filename_6 = $config['rrd_dir'] . "/" . $hostname . "/ipSystemStats-ipv6.rrd";

  $rrd_options .= " DEF:InForwDatagrams_4=$rrd_filename_4:InForwDatagrams:AVERAGE";
  $rrd_options .= " DEF:OutForwDatagrams_4=$rrd_filename_4:OutForwDatagrams:AVERAGE";
  $rrd_options .= " DEF:InDelivers_4=$rrd_filename_4:InDelivers:AVERAGE";
  $rrd_options .= " DEF:InReceives_4=$rrd_filename_4:InReceives:AVERAGE";
  $rrd_options .= " DEF:OutRequests_4=$rrd_filename_4:OutRequests:AVERAGE";
  $rrd_options .= " DEF:InDiscards_4=$rrd_filename_4:InDiscards:AVERAGE";
  $rrd_options .= " DEF:OutDiscards_4=$rrd_filename_4:OutDiscards:AVERAGE";
  $rrd_options .= " DEF:OutNoRoutes_4=$rrd_filename_4:InDiscards:AVERAGE";

  $rrd_options .= " DEF:InForwDatagrams_6=$rrd_filename_6:InForwDatagrams:AVERAGE";
  $rrd_options .= " DEF:OutForwDatagrams_6=$rrd_filename_6:OutForwDatagrams:AVERAGE";
  $rrd_options .= " DEF:InDelivers_6=$rrd_filename_6:InDelivers:AVERAGE";
  $rrd_options .= " DEF:InReceives_6=$rrd_filename_6:InReceives:AVERAGE";
  $rrd_options .= " DEF:OutRequests_6=$rrd_filename_6:OutRequests:AVERAGE";
  $rrd_options .= " DEF:InDiscards_6=$rrd_filename_6:InDiscards:AVERAGE";
  $rrd_options .= " DEF:OutDiscards_6=$rrd_filename_6:OutDiscards:AVERAGE";
  $rrd_options .= " DEF:OutNoRoutes_6=$rrd_filename_6:InDiscards:AVERAGE";

  $rrd_options .= " COMMENT:'Packets/sec       Current  Average  Maximum\\n'";
  $rrd_options .= " LINE1.25:InForwDatagrams_4#cc0000:InForwDgrams.4";
  $rrd_options .= " GPRINT:InForwDatagrams_4:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:InForwDatagrams_4:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:InForwDatagrams_4:MAX:%6.2lf%s\\\\n";

  $rrd_options .= " LINE1.25:InForwDatagrams_6#cc0000:'            .6'";
  $rrd_options .= " GPRINT:InForwDatagrams_6:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:InForwDatagrams_6:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:InForwDatagrams_6:MAX:%6.2lf%s\\\\n";

  $rrd_options .= " LINE1.25:InDelivers_4#00cc00:'InDelivers  .4'";
  $rrd_options .= " GPRINT:InDelivers_4:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:InDelivers_4:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:InDelivers_4:MAX:%6.2lf%s\\\\n";

  $rrd_options .= " LINE1.25:InDelivers_6#00cc00:'            .6'";
  $rrd_options .= " GPRINT:InDelivers_6:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:InDelivers_6:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:InDelivers_6:MAX:%6.2lf%s\\\\n";

  $rrd_options .= " LINE1.25:InReceives_4#006600:'InReceives  .4'";
  $rrd_options .= " GPRINT:InReceives_4:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:InReceives_4:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:InReceives_4:MAX:%6.2lf%s\\\\n";

  $rrd_options .= " LINE1.25:InReceives_6#006600:'            .6'";
  $rrd_options .= " GPRINT:InReceives_6:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:InReceives_6:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:InReceives_6:MAX:%6.2lf%s\\\\n";

  $rrd_options .= " LINE1.25:OutRequests_4#0000cc:'OutRequests .4'";
  $rrd_options .= " GPRINT:OutRequests_4:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:OutRequests_4:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:OutRequests_4:MAX:%6.2lf%s\\\\n";

  $rrd_options .= " LINE1.25:OutRequests_6#0000cc:'            .6'";
  $rrd_options .= " GPRINT:OutRequests_6:LAST:%6.2lf%s";
  $rrd_options .= " GPRINT:OutRequests_6:AVERAGE:%6.2lf%s";
  $rrd_options .= " GPRINT:OutRequests_6:MAX:%6.2lf%s\\\\n";

?>
