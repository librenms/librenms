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
$rrdfilename = Rrd::name($device['hostname'], 'canopy-generic-frameUtilization');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'%                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:downlinkutilization=' . $rrdfilename . ':downlinkutilization:AVERAGE ';
    $rrd_options .= ' DEF:uplinkutilization=' . $rrdfilename . ':uplinkutilization:AVERAGE ';
    $rrd_options .= " LINE2:downlinkutilization#FF0000:'Downlink Frame Utilization       ' ";
    $rrd_options .= ' GPRINT:downlinkutilization:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:downlinkutilization:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:downlinkutilization:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:uplinkutilization#003EFF:'Uplink Frame Utilization      ' ";
    $rrd_options .= ' GPRINT:uplinkutilization:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:uplinkutilization:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:uplinkutilization:MAX:%0.2lf%s\\\l ';
}
