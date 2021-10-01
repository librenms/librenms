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

$rrdfilename = Rrd::name($device['hostname'], 'cambium-250-receivePower');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:receivePower=' . $rrdfilename . ':receivePower:AVERAGE ';
    $rrd_options .= ' DEF:noiseFloor=' . $rrdfilename . ':noiseFloor:AVERAGE ';
    $rrd_options .= " LINE2:receivePower#00FF00:'Receive Power         ' ";
    $rrd_options .= ' GPRINT:receivePower:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:receivePower:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:receivePower:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:noiseFloor#000000:'Noise Floor      ' ";
    $rrd_options .= ' GPRINT:noiseFloor:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:noiseFloor:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:noiseFloor:MAX:%0.2lf%s\\\l ';
}
