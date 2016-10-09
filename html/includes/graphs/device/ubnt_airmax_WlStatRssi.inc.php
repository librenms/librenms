<?php

require 'includes/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dbm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:WlStatRssi='.$rrdfilename.':WlStatRssi:AVERAGE ';
    $rrd_options .= " LINE1:WlStatRssi#CC0000:'RSSI                 ' ";
    $rrd_options .= ' GPRINT:WlStatRssi:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:WlStatRssi:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:WlStatRssi:MAX:%3.2lf\\\l ';
}
