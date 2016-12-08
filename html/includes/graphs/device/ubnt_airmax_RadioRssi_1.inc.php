<?php

require 'includes/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dbm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:RadioRssi_1='.$rrdfilename.':RadioRssi_1:AVERAGE ';
    $rrd_options .= " LINE1:RadioRssi_1#00FF00:'RSSI                 ' ";
    $rrd_options .= ' GPRINT:RadioRssi_1:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:RadioRssi_1:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:RadioRssi_1:MAX:%3.2lf\\\l ';
}
