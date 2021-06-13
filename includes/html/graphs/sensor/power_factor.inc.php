<?php

// Power factor can be positive (drawing power from a source) or negative (feeding power to a sink).
// However, most equipment only deals with power in a single direction so we can overwrite the defaults where relevant.
is_numeric($sensor['sensor_limit_low']) ? $scale_min = $sensor['sensor_limit_low'] : $scale_min = '-1';
is_numeric($sensor['sensor_limit']) ? $scale_max = $sensor['sensor_limit'] : $scale_max = '1';

require 'includes/html/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                                     Min     Max     Last\\n'";
$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " LINE1.5:sensor#cc0000:'" . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor['sensor_descr'], 30) . "'";
$rrd_options .= ' GPRINT:sensor:MIN:%1.4lf';
$rrd_options .= ' GPRINT:sensor:MAX:%1.4lf';
$rrd_options .= ' GPRINT:sensor:LAST:%1.4lf\l';

if (is_numeric($sensor['sensor_limit'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit'] . '#999999::dashes';
}

$rrd_options .= ' HRULE:0#999999';

if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' HRULE:' . $sensor['sensor_limit_low'] . '#999999::dashes';
}
