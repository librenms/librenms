<?php

require 'includes/html/graphs/common.inc.php';

$mysql_rrd = Rrd::name($device['hostname'], ['app', 'mysql', $app['app_id']]);

if (Rrd::checkRrdExists($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}

$rrd_options .= ' DEF:a=' . $rrd_filename . ':IDBLBSe:AVERAGE ';
$rrd_options .= ' DEF:b=' . $rrd_filename . ':IBLFh:AVERAGE ';
$rrd_options .= ' DEF:c=' . $rrd_filename . ':IBLWn:AVERAGE ';

$rrd_options .= 'COMMENT:"            Current    Average   Maximum\n" ';

$rrd_options .= 'AREA:a#FAFD9E:"Buffer Size  "      ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\\n"  ';

$rrd_options .= 'LINE1:b#22FF22:"KB Flushed "  ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\\n"  ';

$rrd_options .= 'LINE1:c#0022FF:"KB Written  "  ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\\n"  ';
