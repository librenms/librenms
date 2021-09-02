<?php

require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' -l 0 -E ';

$rrdfilename = Rrd::name($device['hostname'], 'ubnt-airfiber-mib');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dbm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:txPower=' . $rrdfilename . ':txPower:AVERAGE ';
    $rrd_options .= " LINE1:txPower#CC0000:'Tx Power             ' ";
    $rrd_options .= ' GPRINT:txPower:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:txPower:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:txPower:MAX:%3.2lf\\\l ';
}
