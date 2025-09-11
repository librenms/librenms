<?php

// This is a workaround to avoid duplicate SI metrics like kkWh.

use LibreNMS\Data\Store\Rrd;
use LibreNMS\Util\Number;

$sensor['sensor_descr_fixed'] = Rrd::fixedSafeDescr($sensor->sensor_descr, 25);

// Next line is a workaround while rrdtool --left-axis-format doesn't support %S
// https://github.com/oetiker/rrdtool-1.x/issues/1271
//$rrd_options .= ' --left-axis-format "%4.1lF%S' . str_replace('%', '%%', $sensor->unit()) . '"';
$rrd_options .= ' --left-axis-format "%5.4lf' . trim(substr(Number::formatSi($sensor->sensor_current * 1000, 0, 0, ''), -1)) . 'Wh"';
$rrd_options .= ' --vertical-label "' . $sensor->classDescr() . '"';
$rrd_options .= ' --units-length=11';
$rrd_options .= ' DEF:t_sensor=' . $rrd_filename . ':sensor:AVERAGE';
$rrd_options .= ' DEF:t_sensor_max=' . $rrd_filename . ':sensor:MAX';
$rrd_options .= ' DEF:t_sensor_min=' . $rrd_filename . ':sensor:MIN';
$rrd_options .= ' CDEF:sensor=t_sensor,1000,*';
$rrd_options .= ' CDEF:sensor_max=t_sensor_max,1000,*';
$rrd_options .= ' CDEF:sensor_min=t_sensor_min,1000,*';
$rrd_options .= ' AREA:sensor_max#c5c5c5';
$rrd_options .= ' AREA:sensor_min#ffffffff';
$rrd_options .= ' COMMENT:"Alert thresholds\:"';
$rrd_options .= ($sensor->sensor_limit_low) ? '  LINE1.5:' . $sensor->sensor_limit_low . '#00008b:"low = ' . Number::formatSi($sensor->sensor_limit_low, 2, 3, $sensor->unit()) . '":dashes' : '';
$rrd_options .= ($sensor->sensor_limit_low_warn) ? ' LINE1.5:' . $sensor->sensor_limit_low_warn . '#005bdf:"low_warn = ' . Number::formatSi($sensor->sensor_limit_low_warn, 2, 3, $sensor->unit()) . '":dashes' : '';
$rrd_options .= ($sensor->sensor_limit_warn) ? (' LINE1.5:' . $sensor->sensor_limit_warn . '#ffa420:"high_warn = ' . Number::formatSi($sensor->sensor_limit_warn, 2, 3, $sensor->unit()) . '":dashes') : '';
$rrd_options .= ($sensor->sensor_limit) ? (' LINE1.5:' . $sensor->sensor_limit . '#ff0000:"high = ' . Number::formatSi($sensor->sensor_limit, 2, 3, $sensor->unit()) . '":dashes') : '';
$rrd_options .= ' COMMENT:"\n"';
$rrd_options .= ' COMMENT:"' . Rrd::fixedSafeDescr('', 25) . '       Now        Avg       Min       Max\n"';
$rrd_options .= ' LINE2:sensor#000000:"' . $sensor->sensor_descr_fixed . '"';
$rrd_options .= ' GPRINT:sensor:LAST:%7.4lf%SWh';
$rrd_options .= ' GPRINT:sensor:AVERAGE:%7.4lf%SWh';
$rrd_options .= ' GPRINT:sensor:MIN:%7.4lf%SWh';
$rrd_options .= ' GPRINT:sensor:MAX:%7.4lf%SWh\\l';

// Linear prediction of trend
if ($to > time()) {
    $rrd_options .= ' VDEF:islope=sensor_max,LSLSLOPE';
    $rrd_options .= ' VDEF:icons=sensor_max,LSLINT';
    $rrd_options .= ' CDEF:ilsl=sensor_max,POP,islope,COUNT,*,icons,+ ';
    $rrd_options .= " LINE2:ilsl#44aa55:'Linear Prediction\\n':dashes=8";
}
