<?php

$scale_min = '25';
$scale_max = '40';

require 'includes/html/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                                 Last   Max\\n'";

$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " DEF:sensor_max=$rrd_filename:sensor:MAX";
$rrd_options .= " DEF:sensor_min=$rrd_filename:sensor:MIN";
$rrd_options .= ' CDEF:sensorcold=sensor_min,20,LT,sensor,UNKN,IF';
$rrd_options .= ' AREA:sensor_min#ffffffff';
$rrd_options .= " LINE1:sensor#cc0000:'" . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor['sensor_descr'], 28) . "'";
$rrd_options .= ' GPRINT:sensor:LAST:%3.0lf%%';
$rrd_options .= ' GPRINT:sensor:MAX:%3.0lf%%\l';

if (is_numeric($sensor['sensor_limit'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit'] . '#999999::dashes';
}

if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit_low'] . '#999999::dashes';
}
