<?php

require 'includes/html/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                                        Min        Max      Last\\n'";
$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " LINE1.5:sensor#cc0000:'" . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor['sensor_descr'], 30) . "'";
$rrd_options .= ' GPRINT:sensor:MIN:%8.0lf';
$rrd_options .= ' GPRINT:sensor:MAX:%8.0lf';
$rrd_options .= ' GPRINT:sensor:LAST:%8.0lf\l';

if (is_numeric($sensor['sensor_limit'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit'] . '#999999::dashes';
}

if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit_low'] . '#999999::dashes';
}
