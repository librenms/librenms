<?php

require 'includes/html/graphs/common.inc.php';

$rrdfilename = Rrd::name($device['hostname'], 'siklu-interface');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'bps      Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:rfInOctets=' . $rrdfilename . ':rfInOctets:AVERAGE ';
    $rrd_options .= ' DEF:rfOutOctets=' . $rrdfilename . ':rfOutOctets:AVERAGE ';
    // $rrd_options .= " CDEF:inoctets=rfInOctets,8,*";
    // $rrd_options .= " CDEF:outoctets=rfOutOctets,8,*";
    $rrd_options .= " LINE1:rfInOctets#00FF00:'In         ' ";
    $rrd_options .= ' GPRINT:rfInOctets:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInOctets:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInOctets:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:rfOutOctets#CC0000:'Out         ' ";
    $rrd_options .= ' GPRINT:rfOutOctets:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfOutOctets:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfOutOctets:MAX:%0.2lf%s\\\l ';
}
