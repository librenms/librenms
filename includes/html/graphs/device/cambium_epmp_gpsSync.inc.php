<?php

/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
require 'includes/html/graphs/common.inc.php';
$rrdfilename = Rrd::name($device['hostname'], 'cambium-epmp-gpsSync');
if (Rrd::checkRrdExists($rrdfilename)) {
    $graph_params->scale_min = 0;
    $graph_params->scale_max = 5;

    $rrd_options[] = 'COMMENT:0 Init  1 No Sync  2 Sync  3 Hold Off  4 Regaining  5 Free Run\\n';
    $rrd_options[] = 'DEF:gpsSync=' . $rrdfilename . ':gpsSync:AVERAGE';
    $rrd_options[] = 'LINE2:gpsSync#666699:GPS Sync State   ';
    $rrd_options[] = 'GPRINT:gpsSync:LAST:%0.2lf%s';
}
