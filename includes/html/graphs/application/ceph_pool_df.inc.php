<?php

require 'includes/html/graphs/common.inc.php';

$ceph_pool_rrd = ceph_rrd('df');

if (Rrd::checkRrdExists($ceph_pool_rrd)) {
    $rrd_filename = $ceph_pool_rrd;
}

$rrd_options .= ' -l 0 -b 1024 ';
$rrd_options .= " 'COMMENT:Bytes         Min         Cur        Max\\n'";

$usedc = 'CC0000';
$availc = '008C00';
$totalc = 'e5e5e5';

if ($vars['pool'] != 'c') {
    $rrd_options .= " DEF:poolfree=$rrd_filename:avail:AVERAGE ";
    $rrd_options .= " DEF:poolused=$rrd_filename:used:AVERAGE ";
    $rrd_options .= ' CDEF:pooltotal=poolused,poolfree,+ ';

    $rrd_options .= ' LINE1:poolused#' . $usedc;
    $rrd_options .= ' LINE1:poolfree#' . $availc . '::STACK';
    $rrd_options .= ' AREA:poolused#' . $usedc . '30:Used ';
    $rrd_options .= ' GPRINT:poolused:MIN:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolused:LAST:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolused:MAX:%7.2lf%sB\n';
    $rrd_options .= ' AREA:poolfree#' . $availc . '30:Free:STACK';
    $rrd_options .= ' GPRINT:poolfree:MIN:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolfree:LAST:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolfree:MAX:%7.2lf%sB\n';
    $rrd_options .= ' LINE1:pooltotal#000000:Total';

    $rrd_options .= ' GPRINT:pooltotal:MIN:%6.2lf%sB';
    $rrd_options .= ' GPRINT:pooltotal:LAST:%7.2lf%sB';
    $rrd_options .= ' GPRINT:pooltotal:MAX:%7.2lf%sB\n';
} else {
    $rrd_options .= " DEF:poolsize=$rrd_filename:avail:AVERAGE ";
    $rrd_options .= " DEF:poolused=$rrd_filename:used:AVERAGE ";
    $rrd_options .= " DEF:poolfree=$rrd_filename:objects:AVERAGE ";

    $rrd_options .= ' LINE1:poolused#' . $usedc;
    $rrd_options .= ' AREA:poolused#' . $usedc . '30:Used';
    $rrd_options .= ' GPRINT:poolused:MIN:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolused:LAST:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolused:MAX:%7.2lf%sB\n';
    $rrd_options .= ' AREA:poolfree#' . $availc . '30:Free:STACK';
    $rrd_options .= ' GPRINT:poolfree:MIN:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolfree:LAST:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolfree:MAX:%7.2lf%sB\n';
    $rrd_options .= ' LINE1:poolsize#000000:Total';
    $rrd_options .= ' GPRINT:poolsize:MIN:%6.2lf%sB';
    $rrd_options .= ' GPRINT:poolsize:LAST:%7.2lf%sB';
    $rrd_options .= ' GPRINT:poolsize:MAX:%7.2lf%sB\n';
}
