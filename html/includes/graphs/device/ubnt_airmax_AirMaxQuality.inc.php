<?php

require 'includes/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                         Now   Min  Max\\n'";
    $rrd_options .= ' DEF:AirMaxQuality='.$rrdfilename.':AirMaxQuality:AVERAGE ';
    $rrd_options .= " LINE1:AirMaxQuality#CC0000:'Percent              ' ";
    $rrd_options .= ' GPRINT:AirMaxQuality:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:AirMaxQuality:MIN:%3.0lf ';
    $rrd_options .= ' GPRINT:AirMaxQuality:MAX:%3.0lf\\\l ';
}
