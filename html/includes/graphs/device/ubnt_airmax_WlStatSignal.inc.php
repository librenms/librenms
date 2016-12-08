<?php

$scale_min = -100;
$scale_max = -20;

require 'includes/graphs/common.inc.php';

//$rrd_options .= " -l 0 -E ";

$rrdfilename  = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dbm                      Now      Min     Max\\n'";
    $rrd_options .= " DEF:WlStatSignal=".$rrdfilename.":WlStatSignal:AVERAGE ";
    $rrd_options .= " LINE1:WlStatSignal#CC0000:'Signal            ' ";
    $rrd_options .= " GPRINT:WlStatSignal:LAST:%3.2lf ";
    $rrd_options .= " GPRINT:WlStatSignal:MIN:%3.2lf ";
    $rrd_options .= " GPRINT:WlStatSignal:MAX:%3.2lf\\\l ";
}
