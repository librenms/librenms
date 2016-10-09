<?php

require 'includes/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Metres                     Now    Min     Max\\n'";
    $rrd_options .= ' DEF:RadioDistance='.$rrdfilename.':RadioDistance:AVERAGE ';
    $rrd_options .= " LINE1:RadioDistance#CC0000:'Distance             ' ";
    $rrd_options .= ' GPRINT:RadioDistance:LAST:%3.2lf%s ';
    $rrd_options .= ' GPRINT:RadioDistance:MIN:%3.2lf%s ';
    $rrd_options .= ' GPRINT:RadioDistance:MAX:%3.2lf%s\\\l ';
}
