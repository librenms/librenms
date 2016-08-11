<?php

require 'includes/graphs/common.inc.php';

$rrd_options .= ' -l 0 -E ';

$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                         Now   Min  Max\\n'";
    $rrd_options .= ' DEF:WlStatStaCount='.$rrdfilename.':WlStatStaCount:AVERAGE ';
    $rrd_options .= " LINE1:WlStatStaCount#CC0000:'Stations             ' ";
    $rrd_options .= ' GPRINT:WlStatStaCount:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:WlStatStaCount:MIN:%3.0lf ';
    $rrd_options .= ' GPRINT:WlStatStaCount:MAX:%3.0lf\\\l ';
}
