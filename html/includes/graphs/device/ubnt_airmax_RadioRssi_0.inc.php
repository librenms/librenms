<?php

require 'includes/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dbm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:RadioRssi_0='.$rrdfilename.':RadioRssi_0:AVERAGE ';
    $rrd_options .= " LINE1:RadioRssi_0#CC0000:'RSSI                 ' ";
    $rrd_options .= ' GPRINT:RadioRssi_0:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:RadioRssi_0:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:RadioRssi_0:MAX:%3.2lf\\\l ';
}
