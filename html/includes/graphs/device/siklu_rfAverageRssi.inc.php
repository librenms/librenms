<?php

require 'includes/graphs/common.inc.php';

$rrdfilename = rrd_name($device['hostname'], 'siklu-wireless');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dbm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:rfAverageRssi='.$rrdfilename.':rfAverageRssi:AVERAGE ';
    $rrd_options .= " LINE1:rfAverageRssi#CC0000:'RSSI                 ' ";
    $rrd_options .= ' GPRINT:rfAverageRssi:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:rfAverageRssi:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:rfAverageRssi:MAX:%3.2lf\\\l ';
}
