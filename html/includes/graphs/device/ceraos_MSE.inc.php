<?php
require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now        Min         Max\r" ';
    $num_radios = explode(' ', $device[features])[0];
    $color = array("CC0000", "00CC00", "0000CC", "CCCCCC");
    for ($i=1; $i <= $num_radios; $i++) {
        $rrd_options .= ' DEF:radio'.$i.'MSE='.$rrdfilename.':radio'.$i.'MSE:AVERAGE ';
        $rrd_options .= ' CDEF:divided'.$i.'MSE=radio'.$i.'MSE,100,/ ';
        $rrd_options .= ' LINE1:divided'.$i.'MSE#'.$color[$i-1].':"Radio '.$i.' MSE\l" ';
        $rrd_options .= ' COMMENT:\u ';
        $rrd_options .= ' GPRINT:divided'.$i.'MSE:LAST:"%0.2lf dB" ';
        $rrd_options .= ' GPRINT:divided'.$i.'MSE:MIN:"%0.2lf dB" ';
        $rrd_options .= ' GPRINT:divided'.$i.'MSE:MAX:"%0.2lf dB\r" ';
    }
}
