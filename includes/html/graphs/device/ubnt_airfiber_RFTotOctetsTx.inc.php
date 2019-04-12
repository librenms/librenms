<?php

require 'includes/html/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airfiber-mib');

if (rrdtool_check_rrd_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Octets                 Now      Min     Max\\n'";
    $rrd_options .= ' DEF:txoctetsAll='.$rrdfilename.':txoctetsAll:AVERAGE ';
    $rrd_options .= " LINE1:txoctetsAll#CC0000:'Tx Octets      ' ";
    $rrd_options .= ' GPRINT:txoctetsAll:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:txoctetsAll:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:txoctetsAll:MAX:%0.2lf%s\\\l ';
}
