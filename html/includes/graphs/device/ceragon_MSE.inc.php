<?php
require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now        Min         Max\r" ';
    $rrd_options .= ' DEF:Radio1MSE='.$rrdfilename.':radio1MSE:AVERAGE ';
    $rrd_options .= ' CDEF:divided1MSE=Radio1MSE,100,/ ';
    $rrd_options .= ' DEF:Radio2MSE='.$rrdfilename.':radio2MSE:AVERAGE ';
    $rrd_options .= ' CDEF:divided2MSE=Radio2MSE,100,/ ';
    $rrd_options .= ' LINE1:divided1MSE#CC0000:"Radio 1  MSE\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:divided1MSE:LAST:"%3.2lf dB" ';
    $rrd_options .= ' GPRINT:divided1MSE:MIN:"%3.2lf dB" ';
    $rrd_options .= ' GPRINT:divided1MSE:MAX:"%3.2lf dB\r" ';
    $rrd_options .= ' LINE1:divided2MSE#00CC00:"Radio 2 MSE\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:divided2MSE:LAST:"%3.2lf dB" ';
    $rrd_options .= ' GPRINT:divided2MSE:MIN:"%3.2lf dB" ';
    $rrd_options .= ' GPRINT:divided2MSE:MAX:"%3.2lf dB\r" ';
}
