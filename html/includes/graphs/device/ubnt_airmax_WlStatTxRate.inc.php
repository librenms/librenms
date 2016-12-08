<?php

require 'includes/graphs/common.inc.php';

// $rrd_options .= " -l 0 -E ";
$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'mbps                   Now      Min       Max\\n'";
    $rrd_options .= ' DEF:WlStatTxRate='.$rrdfilename.':WlStatTxRate:AVERAGE ';
    $rrd_options .= ' CDEF:WlStatTxRateC=WlStatTxRate,1000,/ ';
    $rrd_options .= " LINE1:WlStatTxRateC#CC0000:'Tx Rate         ' ";
    $rrd_options .= ' GPRINT:WlStatTxRateC:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:WlStatTxRateC:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:WlStatTxRateC:MAX:%0.2lf%s\\\l ';
}
