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
$rrdfilename = Rrd::name($device['hostname'], 'cambium-epmp-modulation');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Value                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:uplinkMCSMode=' . $rrdfilename . ':uplinkMCSMode:AVERAGE ';
    $rrd_options .= ' DEF:downlinkMCSMode=' . $rrdfilename . ':downlinkMCSMode:AVERAGE ';
    $rrd_options .= " LINE2:uplinkMCSMode#8F5E99:'Uplink       ' ";
    $rrd_options .= ' GPRINT:uplinkMCSMode:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:uplinkMCSMode:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:uplinkMCSMode:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:downlinkMCSMode#0000FF:'Downlink     ' ";
    $rrd_options .= ' GPRINT:downlinkMCSMode:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:downlinkMCSMode:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:downlinkMCSMode:MAX:%0.2lf%s\\\l ';
}
