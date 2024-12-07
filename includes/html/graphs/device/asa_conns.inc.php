<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ fua http://www.lathwood.co.uk>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'asa_conns');

$rrd_options .= " DEF:connections=$rrd_filename:connections:AVERAGE";
$rrd_options .= " DEF:connections_max=$rrd_filename:connections:MAX";
$rrd_options .= " DEF:connections_min=$rrd_filename:connections:MIN";
$rrd_options .= ' AREA:connections_min';

$rrd_options .= " LINE1.5:connections#cc0000:'Current connections'";
$rrd_options .= ' GPRINT:connections_min:MIN:%4.0lf';
$rrd_options .= ' GPRINT:connections:LAST:%4.0lf';
$rrd_options .= ' GPRINT:connections_max:MAX:%4.0lf\l';
