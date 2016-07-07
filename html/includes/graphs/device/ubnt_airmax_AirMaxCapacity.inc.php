<?php

require 'includes/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                         Now   Min  Max\\n'";
    $rrd_options .= ' DEF:AirMaxCapacity='.$rrdfilename.':AirMaxCapacity:AVERAGE ';
    $rrd_options .= " LINE1:AirMaxCapacity#CC0000:'Percent              ' ";
    $rrd_options .= ' GPRINT:AirMaxCapacity:LAST:%3.0lf ';
    $rrd_options .= ' GPRINT:AirMaxCapacity:MIN:%3.0lf ';
    $rrd_options .= ' GPRINT:AirMaxCapacity:MAX:%3.0lf\\\l ';
}
