<?php

use LibreNMS\Config;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Util\Number;

$sensor_descr_fixed = Rrd::fixedSafeDescr($sensor->sensor_descr, 25);
$sensor_color = (Config::get('applied_site_style') == 'dark') ? '#f2f2f2' : '#272b30';
$background_color = (Config::get('applied_site_style') == 'dark') ? '#272b30' : '#ffffff';
$variance_color = (Config::get('applied_site_style') == 'dark') ? '#3e444c' : '#c5c5c5';
$unit_label = str_replace('%', '%%', $sensor->unit());

// Next line is a workaround while rrdtool --left-axis-format doesn't support %S
// https://github.com/oetiker/rrdtool-1.x/issues/1271
$rrd_options .= ' --left-axis-format "%5.1lf' . trim(substr(Number::formatSi($sensor->sensor_current, 0, 0, ''), -1) . $unit_label) . '"';
$rrd_options .= ' --vertical-label "' . $sensor->classDescr() . '"';
$rrd_options .= ' DEF:sensor=' . $rrd_filename . ':sensor:AVERAGE';
$rrd_options .= ' DEF:sensor_max=' . $rrd_filename . ':sensor:MAX';
$rrd_options .= ' DEF:sensor_min=' . $rrd_filename . ':sensor:MIN';
$rrd_options .= ' AREA:sensor_max' . $variance_color;
$rrd_options .= ' AREA:sensor_min' . $background_color;
$field = 'sensor';
if ($unit_label == '°F') {
    $rrd_options .= ' CDEF:far=9,5,/,sensor,*,32,+ ';
    $field = 'far';
}

if ($sensor->hasThresholds()) {
    $rrd_options .= ' COMMENT:"Alert thresholds\:"';
    $rrd_options .= ($sensor->sensor_limit_low !== null) ? '  LINE1.5:' . $sensor->sensor_limit_low . '#00008b:"low = ' . $sensor->formatValue('sensor_limit_low') . '":dashes' : '';
    $rrd_options .= ($sensor->sensor_limit_low_warn !== null) ? ' LINE1.5:' . $sensor->sensor_limit_low_warn . '#005bdf:"low_warn = ' . $sensor->formatValue('sensor_limit_low_warn') . '":dashes' : '';
    $rrd_options .= ($sensor->sensor_limit_warn !== null) ? (' LINE1.5:' . $sensor->sensor_limit_warn . '#ffa420:"high_warn = ' . $sensor->formatValue('sensor_limit_warn') . '":dashes') : '';
    $rrd_options .= ($sensor->sensor_limit !== null) ? (' LINE1.5:' . $sensor->sensor_limit . '#ff0000:"high = ' . $sensor->formatValue('sensor_limit') . '":dashes') : '';
}

// Workaround because rrdtool has trouble detecting the
// range if the sensor is constant and no thresholds are
// defined, so it's forced to +-1% of the min/max.
if ($sensor->doesntHaveThresholds()) {
    $rrd_options .= ' CDEF:canvas_max=sensor_max,1.01,*';
    $rrd_options .= ' LINE1:canvas_max#000000ff::dashes'; // Hidden for scale only
    $rrd_options .= ' CDEF:canvas_min=sensor_min,0.99,*';
    $rrd_options .= ' LINE1:canvas_min#000000ff::dashes'; // Hidden for scale only
}

$rrd_options .= ' COMMENT:"\n"';
$rrd_options .= ' COMMENT:"' . Rrd::fixedSafeDescr('', 25) . '       Now        Avg       Min       Max\n"';
$rrd_options .= ' LINE2:' . $field . $sensor_color . ':"' . $sensor_descr_fixed . '"';
$rrd_options .= ' GPRINT:' . $field . ':LAST:%7.1lf%S' . $unit_label;
$rrd_options .= ' GPRINT:' . $field . ':AVERAGE:%7.1lf%S' . $unit_label;
$rrd_options .= ' GPRINT:' . $field . ':MIN:%7.1lf%S' . $unit_label;
$rrd_options .= ' GPRINT:' . $field . ':MAX:%7.1lf%S' . $unit_label . '\\l';

// Linear prediction of trend
if ($to > time()) {
    $rrd_options .= ' VDEF:islope=sensor_max,LSLSLOPE';
    $rrd_options .= ' VDEF:icons=sensor_max,LSLINT';
    $rrd_options .= ' CDEF:ilsl=sensor_max,POP,islope,COUNT,*,icons,+ ';
    $rrd_options .= " LINE2:ilsl#44aa55:'Linear Prediction\\n':dashes=8";
}

unset($sensor_descr_fixed, $sensor_color, $background_color, $variance_color);
