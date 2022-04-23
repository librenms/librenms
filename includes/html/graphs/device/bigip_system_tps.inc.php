<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = '0';
$descr = 'SSL TPS';
$rrd = Rrd::name($device['hostname'], 'bigip_system_tps');
if (Rrd::checkRrdExists($rrd)) {
    $rrd_options .= ' DEF:a="' . $rrd . '":"TotNativeConns":AVERAGE';
    $rrd_options .= ' DEF:b="' . $rrd . '":"TotCompatConns":AVERAGE';
    $rrd_options .= ' CDEF:cdefi="a,b,-"';
    $rrd_options .= ' LINE2:a#002A97FF:"Native"';
    $rrd_options .= ' GPRINT:a:LAST:"    Last\:%8.2lf %s"';
    $rrd_options .= ' GPRINT:a:AVERAGE:"Avg\:%8.2lf %s"';
    $rrd_options .= ' GPRINT:a:MAX:"Max\:%8.2lf %s\n"';
    $rrd_options .= ' LINE2:b#4444FFFF:"Compat"';
    $rrd_options .= ' GPRINT:b:LAST:"    Last\:%8.2lf %s"';
    $rrd_options .= ' GPRINT:b:AVERAGE:"Avg\:%8.2lf %s"';
    $rrd_options .= ' GPRINT:b:MAX:"Max\:%8.2lf %s\n"';
    $rrd_options .= ' AREA:cdefi#C0C0C0FF:"Difference"';
    $rrd_options .= ' GPRINT:cdefi:LAST:"Last\:%8.2lf %s"';
    $rrd_options .= ' GPRINT:cdefi:AVERAGE:"Avg\:%8.2lf %s"';
    $rrd_options .= ' GPRINT:cdefi:MAX:"Max\:%8.2lf %s\n"';
}
