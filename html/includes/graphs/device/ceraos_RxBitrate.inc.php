<?php
$scale_min = 100;
$scale_max = 250;

require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now          Min          Max\r" ';
    $features = explode(' ', $device[features]);
    $num_radios = $features[0];
    $color = array("CC0000", "00CC00", "0000CC", "CCCCCC");
    for ($i=1; $i <= $num_radios; $i++) {
        $rrd_options .= ' DEF:radio'.$i.'RxBitrate='.$rrdfilename.':radio'.$i.'RxRate:AVERAGE ';
        $rrd_options .= ' CDEF:radio'.$i.'RxBitrateMbps=radio'.$i.'RxBitrate,1000,/ ';
        $rrd_options .= ' LINE1:radio'.$i.'RxBitrateMbps#'.$color[$i-1].':"Radio '.$i.' RxBitrate\l" ';
        $rrd_options .= ' COMMENT:\u ';
        $rrd_options .= ' GPRINT:radio'.$i.'RxBitrateMbps:LAST:"%0.2lf Mbps" ';
        $rrd_options .= ' GPRINT:radio'.$i.'RxBitrateMbps:MIN:"%0.2lf Mbps" ';
        $rrd_options .= ' GPRINT:radio'.$i.'RxBitrateMbps:MAX:"%0.2lf Mbps\r" ';
    }
}
