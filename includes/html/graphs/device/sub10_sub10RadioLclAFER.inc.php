<?php

$scale_max = 100;

require 'includes/html/graphs/common.inc.php';

$rrdfilename = rrd_name($device['hostname'], 'sub10systems');


if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                           Now   Min    Max\\n'";
    $rrd_options .= ' DEF:sub10RadioLclAFER='.$rrdfilename.':sub10RadioLclAFER:AVERAGE ';
    $rrd_options .= " LINE1:sub10RadioLclAFER#CC0000:'Percent               ' ";
    $rrd_options .= ' GPRINT:sub10RadioLclAFER:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclAFER:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclAFER:MAX:%3.2lf\\\l ';
}
