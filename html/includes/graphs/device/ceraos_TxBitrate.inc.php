<?php
$scale_min = 100;
$scale_max = 250;

require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now          Min           Max\r" ';
    $features = explode(' ', $device[features]);
    $num_radios = $features[0];
    $color = array("CC0000", "00CC00", "0000CC", "CCCCCC");
    for ($i=1; $i <= $num_radios; $i++) {
        $rrd_options .= ' DEF:radio'.$i.'TxBitrate='.$rrdfilename.':radio'.$i.'TxRate:AVERAGE ';
        $rrd_options .= ' CDEF:radio'.$i.'TxBitrateMbps=radio'.$i.'TxBitrate,1000,/ ';
        $rrd_options .= ' LINE1:radio'.$i.'TxBitrateMbps#'.$color[$i-1].':"Radio '.$i.' TxBitrate\l" ';
        $rrd_options .= ' COMMENT:\u ';
        $rrd_options .= ' GPRINT:radio'.$i.'TxBitrateMbps:LAST:"%0.2lf Mbps" ';
        $rrd_options .= ' GPRINT:radio'.$i.'TxBitrateMbps:MIN:"%0.2lf Mbps" ';
        $rrd_options .= ' GPRINT:radio'.$i.'TxBitrateMbps:MAX:"%0.2lf Mbps\r" ';
    }
}
