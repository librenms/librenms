<?php

require 'includes/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'mbps                   Now      Min       Max\\n'";
    $rrd_options .= ' DEF:WlStatRxRate='.$rrdfilename.':WlStatRxRate:AVERAGE ';
    $rrd_options .= ' CDEF:WlStatRxRateC=WlStatRxRate,1000,/ ';
    $rrd_options .= " LINE1:WlStatRxRateC#00FF00:'Rx Rate         ' ";
    $rrd_options .= ' GPRINT:WlStatRxRateC:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:WlStatRxRateC:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:WlStatRxRateC:MAX:%0.2lf%s\\\l ';
}
