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
$rrdfilename = Rrd::name($device['hostname'], 'cambium-epmp-gps');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:numTracked=' . $rrdfilename . ':numTracked:AVERAGE ';
    $rrd_options .= ' DEF:numVisible=' . $rrdfilename . ':numVisible:AVERAGE ';
    $rrd_options .= " AREA:numTracked#00B200:'GPS Number Tracked    ' ";
    $rrd_options .= ' GPRINT:numTracked:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:numTracked:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:numTracked:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:numVisible#FFFF00:'GPS Number Visible    ' ";
    $rrd_options .= ' GPRINT:numVisible:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:numVisible:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:numVisible:MAX:%0.2lf%s\\\l ';
}
