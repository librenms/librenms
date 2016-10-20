<?php
require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now          Min           Max\r" ';
    $rrd_options .= ' DEF:radio1TxBitrate='.$rrdfilename.':radio1TxRate:AVERAGE ';
    $rrd_options .= ' DEF:radio2TxBitrate='.$rrdfilename.':radio2TxRate:AVERAGE ';
    $rrd_options .= ' CDEF:radio1TxBitrateMbps=radio1TxBitrate,1000,/ ';
    $rrd_options .= ' CDEF:radio2TxBitrateMbps=radio2TxBitrate,1000,/ ';
    $rrd_options .= ' LINE1:radio1TxBitrateMbps#CC0000:"Radio 1 TxBitrate\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:radio1TxBitrateMbps:LAST:"%0.2lf Mbps" ';
    $rrd_options .= ' GPRINT:radio1TxBitrateMbps:MIN:"%0.2lf Mbps" ';
    $rrd_options .= ' GPRINT:radio1TxBitrateMbps:MAX:"%0.2lf Mbps\r" ';
    $rrd_options .= ' LINE1:radio2TxBitrateMbps#00CC00:"Radio 2 TxBitrate\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:radio2TxBitrateMbps:LAST:"%0.2lf Mbps" ';
    $rrd_options .= ' GPRINT:radio2TxBitrateMbps:MIN:"%0.2lf Mbps" ';
    $rrd_options .= ' GPRINT:radio2TxBitrateMbps:MAX:"%0.2lf Mbps\r" ';
}

?>
