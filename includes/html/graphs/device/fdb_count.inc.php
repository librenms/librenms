<?php

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'fdb_count');

$rrd_options .= " DEF:value=$rrd_filename:value:AVERAGE";
$rrd_options .= " DEF:value_min=$rrd_filename:value:MIN";
$rrd_options .= " DEF:value_max=$rrd_filename:value:MAX";

$rrd_options .= " COMMENT:'MACs      Current  Minimum  Maximum  Average\\n'";
$rrd_options .= ' AREA:value#EEEEEE:value';
$rrd_options .= ' LINE1.25:value#36393D:';
$rrd_options .= " 'GPRINT:value:LAST:%6.2lf ' 'GPRINT:value_min:MIN:%6.2lf '";
$rrd_options .= " 'GPRINT:value_max:MAX:%6.2lf ' 'GPRINT:value:AVERAGE:%6.2lf\\n'";
