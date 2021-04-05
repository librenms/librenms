<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Config;

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'ping-perf');

$rrd_options .= ' DEF:ping=' . $rrd_filename . ':ping:AVERAGE';
$rrd_options .= " 'COMMENT:Milliseconds      Cur      Min     Max     Avg\\n'";
if (Config::get('applied_site_style') == 'dark') {
    $rrd_options .= ' LINE1.25:ping#63636d:Ping';
} else {
    $rrd_options .= ' LINE1.25:ping#36393d:Ping';
}
$rrd_options .= ' GPRINT:ping:LAST:%14.2lf  GPRINT:ping:AVERAGE:%6.2lf';
$rrd_options .= " GPRINT:ping:MAX:%6.2lf  'GPRINT:ping:AVERAGE:%6.2lf\\n'";

if ($_GET['previous'] == 'yes') {
    $rrd_options .= " COMMENT:' \\n'";
    $rrd_options .= " DEF:pingX=$rrd_filename:ping:AVERAGE:start=$prev_from:end=$from";
    $rrd_options .= " SHIFT:pingX:$period";
    $rrd_options .= " LINE1.25:pingX#CCCCCC:'Prev Ping'\t\t";
    $rrd_options .= ' GPRINT:pingX:AVERAGE:%6.2lf';
    $rrd_options .= " GPRINT:pingX:MAX:%6.2lf  'GPRINT:pingX:AVERAGE:%6.2lf\\n'";
}
