<?php

require 'includes/html/graphs/common.inc.php';

$rrdfilename = Rrd::name($device['hostname'], 'siklu-interface');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'bps      Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:rfInGoodOctets=' . $rrdfilename . ':rfInGoodOctets:AVERAGE ';
    $rrd_options .= ' DEF:rfInErroredOctets=' . $rrdfilename . ':rfInErroredOctets:AVERAGE ';
    $rrd_options .= ' DEF:rfInIdleOctets=' . $rrdfilename . ':rfInIdleOctets:AVERAGE ';
    $rrd_options .= ' DEF:rfOutIdleOctets=' . $rrdfilename . ':rfOutIdleOctets:AVERAGE ';
    $rrd_options .= " LINE1:rfInGoodOctets#00FF00:'Good Octets         ' ";
    $rrd_options .= ' GPRINT:rfInGoodOctets:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInGoodOctets:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInGoodOctets:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:rfInErroredOctets#CC0000:'Errored Octets         ' ";
    $rrd_options .= ' GPRINT:rfInErroredOctets:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInErroredOctets:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInErroredOctets:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:rfInIdleOctets#0022FF:'In Idle Octets         ' ";
    $rrd_options .= ' GPRINT:rfInIdleOctets:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInIdleOctets:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInIdleOctets:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:rfOutIdleOctets#DD9CFB:'Out Idle Octets         ' ";
    $rrd_options .= ' GPRINT:rfOutIdleOctets:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfOutIdleOctets:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfOutIdleOctets:MAX:%0.2lf%s\\\l ';
}//end if
