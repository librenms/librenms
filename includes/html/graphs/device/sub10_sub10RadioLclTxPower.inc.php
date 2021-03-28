<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrdfilename = Rrd::name($device['hostname'], 'sub10systems');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:sub10RadioLclTxPowe=' . $rrdfilename . ':sub10RadioLclTxPowe:AVERAGE ';
    $rrd_options .= " LINE1:sub10RadioLclTxPowe#CC0000:'Tx Power         ' ";
    $rrd_options .= ' GPRINT:sub10RadioLclTxPowe:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclTxPowe:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclTxPowe:MAX:%3.2lf\\\l ';
}
