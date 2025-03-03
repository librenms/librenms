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

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';
$rrdfilename = Rrd::name($device['hostname'], 'canopy-generic-regCount');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:regCount=' . $rrdfilename . ':regCount:AVERAGE ';
    $rrd_options .= ' DEF:failed=' . $rrdfilename . ':failed:AVERAGE ';
    $rrd_options .= " AREA:regCount#FF0000:'Registered Sm       ' ";
    $rrd_options .= ' GPRINT:regCount:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:regCount:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:regCount:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:failed#000000:'Amount Failed      ' ";
    $rrd_options .= ' GPRINT:failed:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:failed:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:failed:MAX:%0.2lf%s\\\l ';
}
