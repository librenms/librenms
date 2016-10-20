<?php
require 'includes/graphs/common.inc.php';
$rrdfilename = rrd_name($device['hostname'], 'ceragon-radio');
if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= ' COMMENT:"  Now        Min         Max\r" ';
    $rrd_options .= ' DEF:Radio1DefectedBlocks='.$rrdfilename.':radio1DefectedBlock:AVERAGE ';
    $rrd_options .= ' DEF:Radio2DefectedBlocks='.$rrdfilename.':radio2DefectedBlock:AVERAGE ';
    $rrd_options .= ' LINE1:Radio1DefectedBlocks#CC0000:"Radio 1 DefectedBlocks\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:Radio1DefectedBlocks:LAST:"%0.2lf " ';
    $rrd_options .= ' GPRINT:Radio1DefectedBlocks:MIN:"%0.2lf " ';
    $rrd_options .= ' GPRINT:Radio1DefectedBlocks:MAX:"%0.2lf \r" ';
    $rrd_options .= ' LINE1:Radio2DefectedBlocks#00CC00:"Radio 2 DefectedBlocks\l" ';
    $rrd_options .= ' COMMENT:\u ';
    $rrd_options .= ' GPRINT:Radio2DefectedBlocks:LAST:"%0.2lf " ';
    $rrd_options .= ' GPRINT:Radio2DefectedBlocks:MIN:"%0.2lf " ';
    $rrd_options .= ' GPRINT:Radio2DefectedBlocks:MAX:"%0.2lf \r" ';
}
?>
