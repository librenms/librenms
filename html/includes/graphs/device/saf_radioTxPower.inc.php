<?php

require 'includes/graphs/common.inc.php';

$rrdfilename = rrd_name($device['hostname'], 'saf-modem-radio');

if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now        Min         Max\r" ';
    $rrd_options .= ' DEF:radioTxPower='.$rrdfilename.':radioTxPower:AVERAGE ';
    $rrd_options .= ' LINE1:radioTxPower#CC0000:"TX Power\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:radioTxPower:LAST:"%3.2lf dBm" ';
    $rrd_options .= ' GPRINT:radioTxPower:MIN:"%3.2lf dBm" ';
    $rrd_options .= ' GPRINT:radioTxPower:MAX:"%3.2lf dBm\r" ';
}
