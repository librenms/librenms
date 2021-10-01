<?php

require 'includes/html/graphs/common.inc.php';

$rrdfilename = Rrd::name($device['hostname'], 'siklu-interface');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'pps      Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:rfInPkts=' . $rrdfilename . ':rfInPkts:AVERAGE ';
    $rrd_options .= ' DEF:rfOutPkts=' . $rrdfilename . ':rfOutPkts:AVERAGE ';
    $rrd_options .= " LINE1:rfInPkts#00FF00:'In         ' ";
    $rrd_options .= ' GPRINT:rfInPkts:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInPkts:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInPkts:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:rfOutPkts#CC0000:'Out         ' ";
    $rrd_options .= ' GPRINT:rfOutPkts:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfOutPkts:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfOutPkts:MAX:%0.2lf%s\\\l ';
}
