<?php

require 'includes/graphs/common.inc.php';

$rrdfilename = rrd_name($device['hostname'], 'saf-modem-radio');

if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now         Min          Max\r" ';
    $rrd_options .= ' DEF:radioRxLevel='.$rrdfilename.':radioRxLevel:AVERAGE ';
    $rrd_options .= ' LINE1:radioRxLevel#CC0000:"RX Level\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:radioRxLevel:LAST:"%3.2lf dBm" ';
    $rrd_options .= ' GPRINT:radioRxLevel:MIN:"%3.2lf dBm" ';
    $rrd_options .= ' GPRINT:radioRxLevel:MAX:"%3.2lf dBm\r" ';
}
