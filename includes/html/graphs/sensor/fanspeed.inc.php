<?php

use LibreNMS\Data\Store\Rrd;

require 'includes/html/graphs/common.inc.php';

$sensor['sensor_descr_fixed'] = Rrd::fixedSafeDescr($sensor['sensor_descr'], 25);

$rrd_options .= ' --vertical-label "' . ucfirst($sensor['sensor_class']) . '"';
$rrd_options .= ' --left-axis-format "%5.0lfrpm"';
$rrd_options .= ' --units-exponent 0';
$rrd_options .= ' --units-length 10';
$rrd_options .= ' DEF:sensor=' . $rrd_filename . ':sensor:AVERAGE';
$rrd_options .= ' DEF:sensor_max=' . $rrd_filename . ':sensor:MAX';
$rrd_options .= ' DEF:sensor_min=' . $rrd_filename . ':sensor:MIN';
$rrd_options .= ' AREA:sensor_max#c5c5c5';
$rrd_options .= ' AREA:sensor_min#ffffffff';
$rrd_options .= ' COMMENT:"Alert tresholds\:"';
if (is_numeric($sensor['sensor_limit_low'])) {
    $rrd_options .= ' LINE1.5:' . $sensor['sensor_limit_low'] . '#00008b:"low = ' . $sensor['sensor_limit_low'] . 'rpm":dashes';
}
if (is_numeric($sensor['sensor_limit_low_warn'])) {
    $rrd_options .= ' LINE1.5:' . $sensor['sensor_limit_low_warn'] . '#005bdf:"warn low = ' . $sensor['sensor_limit_low_warn'] . 'rpm":dashes';
}
if (is_numeric($sensor['sensor_limit_warn'])) {
    $rrd_options .= ' LINE1.5:' . $sensor['sensor_limit_warn'] . '#ffa420:"warn high = ' . $sensor['sensor_limit_warn'] . 'rpm":dashes';
}
if (is_numeric($sensor['sensor_limit'])) {
	$rrd_options .= ' LINE1.5:' . $sensor['sensor_limit'] . '#ff0000:"high = ' . $sensor['sensor_limit'] . 'rpm":dashes';
}

$rrd_options .= ' COMMENT:"\n"';

$rrd_options .= ' COMMENT:"' . Rrd::fixedSafeDescr('', 25) . '       Now        Avg       Min       Max\n"';
$rrd_options .= ' LINE2:sensor#000000:"' . $sensor['sensor_descr_fixed'] . '"';
$rrd_options .= ' GPRINT:sensor:LAST:%5.0lfrpm';
$rrd_options .= ' GPRINT:sensor:AVERAGE:%5.0lfrpm';
$rrd_options .= ' GPRINT:sensor:MIN:%5.0lfrpm';
$rrd_options .= ' GPRINT:sensor:MAX:%5.0lfrpm\\l';
