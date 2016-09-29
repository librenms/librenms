<?php

require 'includes/graphs/common.inc.php';

$rrdfilename = $config['rrd_dir'].'/'.$device['hostname'].'/saf.rrd';

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                                              Now          Min           Max\\n'";
    $rrd_options .= ' DEF:modemACMCapacity='.$rrdfilename.':modemACMCapacity:AVERAGE ';
    $rrd_options .= ' DEF:modemTotalCapacity='.$rrdfilename.':modemTotalCapacity:AVERAGE ';
    $rrd_options .= ' CDEF:acmCapacityMbps=modemACMCapacity,1000,* ';
    $rrd_options .= ' CDEF:capacityMbps=modemTotalCapacity,1000,* ';
    $rrd_options .= " LINE1:acmCapacityMbps#00CC00:'ACM Total Capacity                ' ";
    $rrd_options .= ' GPRINT:acmCapacityMbps:LAST:"%0.2lf %sbps" ';
    $rrd_options .= ' GPRINT:acmCapacityMbps:MIN:"%0.2lf %sbps" ';
    $rrd_options .= ' GPRINT:acmCapacityMbps:MAX:"%0.2lf %sbps\\\l" ';
    $rrd_options .= " LINE1:capacityMbps#CC0000:'Total Capacity                    ' ";
    $rrd_options .= ' GPRINT:capacityMbps:LAST:"%0.2lf %sbps" ';
    $rrd_options .= ' GPRINT:capacityMbps:MIN:"%0.2lf %sbps" ';
    $rrd_options .= ' GPRINT:capacityMbps:MAX:"%0.2lf %sbps\\\l" ';
}
