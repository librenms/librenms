<?php

require 'includes/html/graphs/common.inc.php';

$rrdfilename = Rrd::name($device['hostname'], 'siklu-interface');

if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'pps      Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:rfInGoodPkts=' . $rrdfilename . ':rfInGoodPkts:AVERAGE ';
    $rrd_options .= ' DEF:rfInErroredPkts=' . $rrdfilename . ':rfInErroredPkts:AVERAGE ';
    $rrd_options .= ' DEF:rfInLostPkts=' . $rrdfilename . ':rfInLostPkts:AVERAGE ';
    $rrd_options .= " LINE1:rfInGoodPkts#00FF00:'Good Pkts         ' ";
    $rrd_options .= ' GPRINT:rfInGoodPkts:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInGoodPkts:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInGoodPkts:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:rfInErroredPkts#CC0000:'Errored Pkts         ' ";
    $rrd_options .= ' GPRINT:rfInErroredPkts:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInErroredPkts:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInErroredPkts:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE1:rfInLostPkts#0022FF:'Lost Pkts         ' ";
    $rrd_options .= ' GPRINT:rfInLostPkts:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInLostPkts:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rfInLostPkts:MAX:%0.2lf%s\\\l ';
}
