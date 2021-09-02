<?php

require 'includes/html/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                      Min    Last    Max\\n'";

$sensor['sensor_descr_fixed'] = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor['sensor_descr'], 15);

$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " DEF:sensor_max=$rrd_filename:sensor:MAX";
$rrd_options .= " DEF:sensor_min=$rrd_filename:sensor:MIN";
$rrd_options .= " LINE1.5:sensor#cc0000:'" . $sensor['sensor_descr_fixed'] . "'";
$rrd_options .= " GPRINT:sensor_min$current_id:MIN:%4.1lf";
$rrd_options .= ' GPRINT:sensor:LAST:%4.1lf';
$rrd_options .= ' GPRINT:sensor_max:MAX:%4.1lf\\l';

if (is_numeric($sensor['sensor_limit'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit'] . '#999999::dashes';
}

if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit_low'] . '#999999::dashes';
}
