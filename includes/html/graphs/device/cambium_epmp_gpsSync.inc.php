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
    $rrd_options .= " COMMENT:'1 - GPS Sync Up       2 - GPS Sync Down      3 - CMM Sync     \\n'";
    $rrd_options .= ' DEF:gpsSync=' . $rrdfilename . ':gpsSync:AVERAGE ';
    $rrd_options .= ' -l 1 ';
    $rrd_options .= ' -u 3 ';
    $rrd_options .= " LINE2:gpsSync#666699:'GPS Sync Status  ' ";
    $rrd_options .= ' GPRINT:gpsSync:LAST:%0.2lf%s ';
}
