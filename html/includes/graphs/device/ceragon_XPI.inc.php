<?php
require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now        Min         Max\r" ';
    $rrd_options .= ' DEF:Radio1XPI='.$rrdfilename.':radio1XPI:AVERAGE ';
    $rrd_options .= ' CDEF:Radio1XPIcalc=Radio1XPI,100,/ ';
    $rrd_options .= ' DEF:Radio2XPI='.$rrdfilename.':radio2XPI:AVERAGE ';
    $rrd_options .= ' CDEF:Radio2XPIcalc=Radio2XPI,100,/ ';
    $rrd_options .= ' LINE1:Radio1XPIcalc#CC0000:"Radio 1  XPI\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:Radio1XPIcalc:LAST:"%0.2lf dB" ';
    $rrd_options .= ' GPRINT:Radio1XPIcalc:MIN:"%0.2lf dB" ';
    $rrd_options .= ' GPRINT:Radio1XPIcalc:MAX:"%0.2lf dB\r" ';
    $rrd_options .= ' LINE1:Radio2XPI#00CC00:"Radio 2 XPI\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:Radio2XPIcalc:LAST:"%0.2lf dB" ';
    $rrd_options .= ' GPRINT:Radio2XPIcalc:MIN:"%0.2lf dB" ';
    $rrd_options .= ' GPRINT:Radio2XPIcalc:MAX:"%0.2lf dB\r" ';
}
