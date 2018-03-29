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

$rrd_filename = rrd_name($device['hostname'], 'poller-perf');

$rrd_options .= ' DEF:poller='.$rrd_filename.':poller:AVERAGE';
$rrd_options .= " 'COMMENT:Seconds      Cur     Min     Max     Avg\\n'";
$rrd_options .= ' LINE1.25:poller#36393D:Poller';
$rrd_options .= ' GPRINT:poller:LAST:%6.2lf  GPRINT:poller:MIN:%6.2lf';
$rrd_options .= " GPRINT:poller:MAX:%6.2lf  'GPRINT:poller:AVERAGE:%6.2lf\\n'";
