<?php

//$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                       Min       Last      Max\\n'";

$sensor['sensor_descr_fixed'] = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor['sensor_descr'], 14);

$rrd_options .= ' -Y';
$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " LINE1.5:sensor#cc0000:'" . $sensor['sensor_descr_fixed'] . "'";
$rrd_options .= " GPRINT:sensor$current_id:MIN:%5.0lfps/nm";
$rrd_options .= ' GPRINT:sensor:LAST:%5.0lfps/nm';
$rrd_options .= ' GPRINT:sensor:MAX:%5.0lfps/nm\l';

if (is_numeric($sensor['sensor_limit'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit'] . '#999999::dashes';
}

if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit_low'] . '#999999::dashes';
}
