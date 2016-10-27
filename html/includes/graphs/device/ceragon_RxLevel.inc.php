<?php
require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now         Min          Max\r" ';
    $rrd_options .= ' DEF:radio1RxLevel='.$rrdfilename.':radio1RxLevel:AVERAGE ';
    $rrd_options .= ' DEF:radio2RxLevel='.$rrdfilename.':radio2RxLevel:AVERAGE ';
    $rrd_options .= ' LINE1:radio1RxLevel#CC0000:"Radio 1 RX Level\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:radio1RxLevel:LAST:"%3.2lf dBm" ';
    $rrd_options .= ' GPRINT:radio1RxLevel:MIN:"%3.2lf dBm" ';
    $rrd_options .= ' GPRINT:radio1RxLevel:MAX:"%3.2lf dBm\r" ';
    $rrd_options .= ' LINE1:radio2RxLevel#00CC00:"Radio 2 RX Level\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:radio2RxLevel:LAST:"%3.2lf dBm" ';
    $rrd_options .= ' GPRINT:radio2RxLevel:MIN:"%3.2lf dBm" ';
    $rrd_options .= ' GPRINT:radio2RxLevel:MAX:"%3.2lf dBm\r" ';
}
