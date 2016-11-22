<?php
$scale_min = -75;
$scale_max = -55;

require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now         Min          Max\r" ';
    $features = explode(' ', $device[features]);
    $num_radios = $features[0];
    $color = array("CC0000", "00CC00", "0000CC", "CCCCCC");
    for ($i=1; $i <= $num_radios; $i++) {
        $rrd_options .= ' DEF:radio'.$i.'RxLevel='.$rrdfilename.':radio'.$i.'RxLevel:AVERAGE ';
        $rrd_options .= ' LINE1:radio'.$i.'RxLevel#'.$color[$i-1].':"Radio '.$i.' RX Level\l" ';
        $rrd_options .= ' COMMENT:\u ';
        $rrd_options .= ' GPRINT:radio'.$i.'RxLevel:LAST:"%3.2lf dBm" ';
        $rrd_options .= ' GPRINT:radio'.$i.'RxLevel:MIN:"%3.2lf dBm" ';
        $rrd_options .= ' GPRINT:radio'.$i.'RxLevel:MAX:"%3.2lf dBm\r" ';
    }
}
