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

$scale_min = '0';

require 'includes/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], 'ping-perf');

$rrd_options .= ' DEF:ping='.$rrd_filename.':ping:AVERAGE';
$rrd_options .= " 'COMMENT:Seconds      Current  Minimum  Maximum  Average\\n'";
$rrd_options .= ' LINE1.25:ping#36393D:Ping';
$rrd_options .= ' GPRINT:ping:LAST:%6.2lf  GPRINT:ping:AVERAGE:%6.2lf';
$rrd_options .= " GPRINT:ping:MAX:%6.2lf  'GPRINT:ping:AVERAGE:%6.2lf\\n'";
