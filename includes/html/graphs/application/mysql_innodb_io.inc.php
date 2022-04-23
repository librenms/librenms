<?php

require 'includes/html/graphs/common.inc.php';

$mysql_rrd = Rrd::name($device['hostname'], ['app', 'mysql', $app['app_id']]);

if (Rrd::checkRrdExists($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;

    $rrd_options .= ' DEF:a=' . $rrd_filename . ':IBIRd:AVERAGE ';
    $rrd_options .= ' DEF:b=' . $rrd_filename . ':IBIWr:AVERAGE ';
    $rrd_options .= ' DEF:c=' . $rrd_filename . ':IBILg:AVERAGE ';
    $rrd_options .= ' DEF:d=' . $rrd_filename . ':IBIFSc:AVERAGE ';

    $rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

    $rrd_options .= 'LINE1:a#22FF22:"File Reads  "    ';
    $rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
    $rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
    $rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\\n"  ';

    $rrd_options .= 'LINE1:b#0022FF:"File Writes  "  ';
    $rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
    $rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
    $rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\\n"  ';

    $rrd_options .= 'LINE1:c#FF0000:"Log Writes  "  ';
    $rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
    $rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
    $rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\\n"  ';

    $rrd_options .= 'LINE1:d#00AAAA:"File syncs  "  ';
    $rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
    $rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
    $rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\\n"  ';
}
