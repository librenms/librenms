<?php
$scale_min = -10;
$scale_max = 20;

require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now        Min         Max\r" ';
    $features = explode(' ', $device[features]);
    $num_radios = $features[0];
    $color = array("CC0000", "00CC00", "0000CC", "CCCCCC");
    for ($i=1; $i <= $num_radios; $i++) {
        $rrd_options .= ' DEF:radio'.$i.'TxPower='.$rrdfilename.':radio'.$i.'TxPower:AVERAGE ';
        $rrd_options .= ' LINE1:radio'.$i.'TxPower#'.$color[$i-1].':"Radio '.$i.' RX Level\l" ';
        $rrd_options .= ' COMMENT:\u ';
        $rrd_options .= ' GPRINT:radio'.$i.'TxPower:LAST:"%3.2lf dBm" ';
        $rrd_options .= ' GPRINT:radio'.$i.'TxPower:MIN:"%3.2lf dBm" ';
        $rrd_options .= ' GPRINT:radio'.$i.'TxPower:MAX:"%3.2lf dBm\r" ';
    }
}
