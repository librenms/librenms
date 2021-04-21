<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'nginx', $app['app_id']]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= ' -b 1000 ';
    $rrd_options .= ' -l 0 ';
    $rrd_options .= ' DEF:a=' . $rrd_filename . ':Requests:AVERAGE';

    $rrd_options .= " COMMENT:'Requests    Current    Average   Maximum\\n'";

    $rrd_options .= ' AREA:a#98FB98';
    $rrd_options .= " LINE1.5:a#006400:'Requests  '";
    $rrd_options .= " GPRINT:a:LAST:'%6.2lf %s'";
    $rrd_options .= " GPRINT:a:AVERAGE:'%6.2lf %s'";
    $rrd_options .= " GPRINT:a:MAX:'%6.2lf %s\\n'";
} else {
    $error_msg = 'Missing RRD';
}
