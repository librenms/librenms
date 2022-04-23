<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename_4 = Rrd::name($device['hostname'], 'ipSystemStats-ipv4');
$rrd_filename_6 = Rrd::name($device['hostname'], 'ipSystemStats-ipv6');

$rrd_options .= " DEF:InForwDatagrams_4=$rrd_filename_4:InForwDatagrams:AVERAGE";
$rrd_options .= " DEF:InDelivers_4=$rrd_filename_4:InDelivers:AVERAGE";
$rrd_options .= " DEF:InReceives_4=$rrd_filename_4:InReceives:AVERAGE";
$rrd_options .= " DEF:InDiscards_4=$rrd_filename_4:InDiscards:AVERAGE";

$rrd_options .= " DEF:OutForwDatagrams_4=$rrd_filename_4:OutForwDatagrams:AVERAGE";
$rrd_options .= ' CDEF:OutForwDatagrams_4_n=OutForwDatagrams_4,-1,*';
$rrd_options .= " DEF:OutRequests_4=$rrd_filename_4:OutRequests:AVERAGE";
$rrd_options .= ' CDEF:OutRequests_4_n=OutRequests_4,-1,*';
$rrd_options .= " DEF:OutDiscards_4=$rrd_filename_4:OutDiscards:AVERAGE";
$rrd_options .= ' CDEF:OutDiscards_4_n=OutDiscards_4,-1,*';
$rrd_options .= " DEF:OutNoRoutes_4=$rrd_filename_4:InDiscards:AVERAGE";
$rrd_options .= ' CDEF:OutNoRoutes_4_n=OutNoRoutes_4,-1,*';

$rrd_options .= " DEF:InForwDatagrams_6=$rrd_filename_6:InForwDatagrams:AVERAGE";
$rrd_options .= " DEF:InDelivers_6=$rrd_filename_6:InDelivers:AVERAGE";
$rrd_options .= " DEF:InReceives_6=$rrd_filename_6:InReceives:AVERAGE";
$rrd_options .= " DEF:InDiscards_6=$rrd_filename_6:InDiscards:AVERAGE";

$rrd_options .= " DEF:OutForwDatagrams_6=$rrd_filename_6:OutForwDatagrams:AVERAGE";
$rrd_options .= ' CDEF:OutForwDatagrams_6_n=OutForwDatagrams_6,-1,*';
$rrd_options .= " DEF:OutRequests_6=$rrd_filename_6:OutRequests:AVERAGE";
$rrd_options .= ' CDEF:OutRequests_6_n=OutRequests_6,-1,*';
$rrd_options .= " DEF:OutDiscards_6=$rrd_filename_6:OutDiscards:AVERAGE";
$rrd_options .= ' CDEF:OutDiscards_6_n=OutDiscards_6,-1,*';
$rrd_options .= " DEF:OutNoRoutes_6=$rrd_filename_6:InDiscards:AVERAGE";
$rrd_options .= ' CDEF:OutNoRoutes_6_n=OutNoRoutes_6,-1,*';

$rrd_options .= " COMMENT:'Packets/sec       Current  Average  Maximum\\n'";

$rrd_options .= " AREA:InReceives_4#CDEB8B:'InReceives   v4'";
$rrd_options .= ' GPRINT:InReceives_4:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:InReceives_4:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:InReceives_4:MAX:%6.2lf%s\\\\n';

$rrd_options .= " AREA:InReceives_6#8C9E5E:'             v6':STACK";
$rrd_options .= ' GPRINT:InReceives_6:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:InReceives_6:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:InReceives_6:MAX:%6.2lf%s\\\\n';

$rrd_options .= " AREA:OutRequests_4_n#C3D9FF:'OutRequests  v4'";
$rrd_options .= ' GPRINT:OutRequests_4:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:OutRequests_4:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:OutRequests_4:MAX:%6.2lf%s\\\\n';

$rrd_options .= " AREA:OutRequests_6_n#8D9CB7:'             v6':STACK";
$rrd_options .= ' GPRINT:OutRequests_6:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:OutRequests_6:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:OutRequests_6:MAX:%6.2lf%s\\\\n';

$rrd_options .= " AREA:InForwDatagrams_4#AF63AF:'InForward    v4'";
$rrd_options .= ' GPRINT:InForwDatagrams_4:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:InForwDatagrams_4:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:InForwDatagrams_4:MAX:%6.2lf%s\\\\n';

$rrd_options .= " AREA:InForwDatagrams_6#3F003F:'             v6':STACK";
$rrd_options .= ' GPRINT:InForwDatagrams_6:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:InForwDatagrams_6:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:InForwDatagrams_6:MAX:%6.2lf%s\\\\n';

$rrd_options .= " AREA:OutForwDatagrams_4#AF63AF:'OutForward   v4'";
$rrd_options .= ' GPRINT:OutForwDatagrams_4:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:OutForwDatagrams_4:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:OutForwDatagrams_4:MAX:%6.2lf%s\\\\n';

$rrd_options .= " AREA:OutForwDatagrams_6#3F003F:'             v6':STACK";
$rrd_options .= ' GPRINT:OutForwDatagrams_6:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:OutForwDatagrams_6:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:OutForwDatagrams_6:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:InDelivers_4#00cc00:'InDelivers   v4'";
$rrd_options .= ' GPRINT:InDelivers_4:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:InDelivers_4:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:InDelivers_4:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:InDelivers_6#3F003F:'             v6'";
$rrd_options .= ' GPRINT:InDelivers_6:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:InDelivers_6:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:InDelivers_6:MAX:%6.2lf%s\\\\n';
