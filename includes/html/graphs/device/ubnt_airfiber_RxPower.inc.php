<?php

require 'includes/html/graphs/common.inc.php';

$rrdfilename = Rrd::name($device['hostname'], 'ubnt-airfibre-mib-rx');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dbm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:rxPower0=' . $rrdfilename . ':rxPower0:AVERAGE ';
    $rrd_options .= ' DEF:rxPower1=' . $rrdfilename . ':rxPower1:AVERAGE ';
    $rrd_options .= " LINE1:rxPower0#00FF00:'Rx Chain0 Power             ' ";
    $rrd_options .= ' GPRINT:rxPower0:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:rxPower0:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:rxPower0:MAX:%3.2lf\\\l ';
    $rrd_options .= " LINE1:rxPower1#CC0000:'Rx Chain1 Power             ' ";
    $rrd_options .= ' GPRINT:rxPower1:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:rxPower1:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:rxPower1:MAX:%3.2lf\\\l ';
}
