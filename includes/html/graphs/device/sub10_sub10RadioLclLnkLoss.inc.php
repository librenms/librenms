<?php

require 'includes/html/graphs/common.inc.php';

$rrdfilename = Rrd::name($device['hostname'], 'sub10systems');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dB                         Now    Min     Max\\n'";
    $rrd_options .= ' DEF:sub10RadioLclLnkLos=' . $rrdfilename . ':sub10RadioLclLnkLos:AVERAGE ';
    $rrd_options .= " LINE1:sub10RadioLclLnkLos#CC0000:'Link Loss            ' ";
    $rrd_options .= ' GPRINT:sub10RadioLclLnkLos:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclLnkLos:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclLnkLos:MAX:%3.2lf\\\l ';
}
