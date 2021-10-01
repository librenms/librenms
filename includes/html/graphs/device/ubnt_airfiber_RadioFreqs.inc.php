<?php

require 'includes/html/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = Rrd::name($device['hostname'], 'ubnt-airfiber-mib');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                       Now     Min      Max\\n'";
    $rrd_options .= ' DEF:rxFrequency=' . $rrdfilename . ':rxFrequency:AVERAGE ';
    $rrd_options .= ' DEF:txFrequency=' . $rrdfilename . ':txFrequency:AVERAGE ';
    $rrd_options .= " LINE1:rxFrequency#00FF00:'Rx Frequency    ' ";
    $rrd_options .= ' GPRINT:rxFrequency:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rxFrequency:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rxFrequency:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:txFrequency#CC0000:'Tx Frequency    ' ";
    $rrd_options .= ' GPRINT:txFrequency:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:txFrequency:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:txFrequency:MAX:%0.2lf%s\\\l ';
}
