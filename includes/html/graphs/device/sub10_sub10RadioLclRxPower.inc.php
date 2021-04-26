<?php

$scale_max = 0;

require 'includes/html/graphs/common.inc.php';

$rrdfilename = Rrd::name($device['hostname'], 'sub10systems');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:sub10RadioLclRxPowe=' . $rrdfilename . ':sub10RadioLclRxPowe:AVERAGE ';
    $rrd_options .= " LINE1:sub10RadioLclRxPowe#CC0000:'Rx Power             ' ";
    $rrd_options .= ' GPRINT:sub10RadioLclRxPowe:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclRxPowe:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclRxPowe:MAX:%3.2lf\\\l ';
}
