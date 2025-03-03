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
$rrdfilename = Rrd::name($device['hostname'], 'canopy-generic-rssi');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:rssi=' . $rrdfilename . ':rssi:AVERAGE ';
    $rrd_options .= " AREA:rssi#FF0000:'RSSI       ' ";
    $rrd_options .= ' GPRINT:rssi:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rssi:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rssi:MAX:%0.2lf%s\\\l ';
}
