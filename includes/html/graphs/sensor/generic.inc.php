<?php

use App\Models\Sensor;
use LibreNMS\Data\Store\Rrd;

require 'includes/html/graphs/common.inc.php';
$sensor = Sensor::find($sensor['sensor_id']);

$sensor['sensor_descr_fixed'] = Rrd::fixedSafeDescr($sensor->sensor_descr, 25);

$rrd_options .= ' --vertical-label "' . $sensor->classDescr() . '"';
$rrd_options .= ' --left-axis-format "%4.1lF' . str_replace('%','%%',$sensor->unit) . '"';
$rrd_options .= ' DEF:sensor='.$rrd_filename.':sensor:AVERAGE';
$rrd_options .= ' DEF:sensor_max='.$rrd_filename.':sensor:MAX';
$rrd_options .= ' DEF:sensor_min='.$rrd_filename.':sensor:MIN';
$rrd_options .= ' AREA:sensor_max#c5c5c5';
$rrd_options .= ' AREA:sensor_min#ffffffff';
$rrd_options .= ' COMMENT:"Alert tresholds\:"';
$rrd_options .= ($sensor->sensor_limit_low) ? '  LINE1.5:' . $sensor->sensor_limit_low . '#00008b:"low = ' . $sensor->sensor_limit_low . $sensor->unit . '":dashes' : '';
$rrd_options .= ($sensor->sensor_limit_low_warn) ? ' LINE1.5:' . $sensor->sensor_limit_low_warn . '#005bdf:"low_warn = ' . $sensor->sensor_limit_low_warn . $sensor->unit . '":dashes' : '';
$rrd_options .= ($sensor->sensor_limit_warn) ? (' LINE1.5:' . $sensor->sensor_limit_warn . '#ffa420:"high_warn = ' . $sensor->sensor_limit_warn . $sensor->unit . '":dashes') : '';
$rrd_options .= ($sensor->sensor_limit) ? (' LINE1.5:' . $sensor->sensor_limit . '#ff0000:"high = ' . $sensor->sensor_limit . $sensor->unit . '":dashes') : '';
$rrd_options .= ' COMMENT:"\n"';
$rrd_options .= ' COMMENT:"'.Rrd::fixedSafeDescr('', 25).'       Now        Avg       Min       Max\n"';
$rrd_options .= ' LINE2:sensor#000000:"' . $sensor->sensor_descr_fixed . '"';
$rrd_options .= ' GPRINT:sensor:LAST:%7.1lf%S' . str_replace('%','%%',$sensor->unit);
$rrd_options .= ' GPRINT:sensor:AVERAGE:%7.1lf%S' . str_replace('%','%%',$sensor->unit);
$rrd_options .= ' GPRINT:sensor:MIN:%7.1lf%S' . str_replace('%','%%',$sensor->unit);
$rrd_options .= ' GPRINT:sensor:MAX:%7.1lf%S' . str_replace('%','%%',$sensor->unit) . '\\l';
