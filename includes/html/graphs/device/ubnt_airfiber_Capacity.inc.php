<?php

require 'includes/html/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = Rrd::name($device['hostname'], 'ubnt-airfiber-mib');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'mbps                   Now      Min       Max\\n'";
    $rrd_options .= ' DEF:rxCapacity=' . $rrdfilename . ':rxCapacity:AVERAGE ';
    $rrd_options .= ' DEF:txCapacity=' . $rrdfilename . ':txCapacity:AVERAGE ';
    $rrd_options .= ' CDEF:rxCapacityC=rxCapacity,1000000,/ ';
    $rrd_options .= ' CDEF:txCapacityC=txCapacity,1000000,/ ';
    $rrd_options .= " LINE1:rxCapacityC#00FF00:'Rx Rate         ' ";
    $rrd_options .= ' GPRINT:rxCapacityC:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rxCapacityC:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rxCapacityC:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:txCapacityC#CC0000:'Tx Rate         ' ";
    $rrd_options .= ' GPRINT:txCapacityC:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:txCapacityC:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:txCapacityC:MAX:%0.2lf%s\\\l ';
}
