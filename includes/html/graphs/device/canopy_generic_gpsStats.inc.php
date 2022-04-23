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
$rrdfilename = Rrd::name($device['hostname'], 'canopy-generic-gpsStats');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Amount                Now       Ave      Max   \\n'";
    $rrd_options .= ' DEF:visible=' . $rrdfilename . ':visible:AVERAGE ';
    $rrd_options .= ' DEF:tracked=' . $rrdfilename . ':tracked:AVERAGE ';
    $rrd_options .= " LINE2:visible#0099ff:'Visible       ' ";
    $rrd_options .= ' GPRINT:visible:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:visible:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:visible:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:tracked#00ff00:'Tracked       ' ";
    $rrd_options .= ' GPRINT:tracked:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:tracked:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:tracked:MAX:%0.2lf%s\\\l ';
}
