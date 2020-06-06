<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2020 Thomas Berberich <https://github.com/SourceDoctor/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Config;

$scale_min = '0';
$scale_max = '100';

require 'includes/html/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], 'availability_1week');

$rrd_options .= ' DEF:availability_1week='.$rrd_filename.':availability_1week:AVERAGE';
$rrd_options .= " 'COMMENT:Availability      Cur      Min     Max     Avg\\n'";
if (Config::get('applied_site_style') == 'dark') {
    $rrd_options .= ' LINE1.25:availability_1week#63636d:%';
} else {
    $rrd_options .= ' LINE1.25:availability_1week#36393d:%';
}
$rrd_options .= ' GPRINT:availability_1week:LAST:%14.3lf  GPRINT:availability_1week:AVERAGE:%6.3lf';
$rrd_options .= " GPRINT:availability_1week:MAX:%6.3lf  'GPRINT:availability_1week:AVERAGE:%6.3lf\\n'";

if ($_GET['previous'] == 'yes') {
    $rrd_options .= " COMMENT:' \\n'";
    $rrd_options .= " DEF:availability_1weekX=$rrd_filename:availability_1week:AVERAGE:start=$prev_from:end=$from";
    $rrd_options .= " SHIFT:availability_1weekX:$period";
    $rrd_options .= " LINE1.25:availability_1weekX#CCCCCC:'Prev Availability'\t\t";
    $rrd_options .= " GPRINT:availability_1weekX:AVERAGE:%6.3lf";
    $rrd_options .= " GPRINT:availability_1weekX:MAX:%6.3lf  'GPRINT:availability_1weekX:AVERAGE:%6.3lf\\n'";
}
