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
$rrdfilename = Rrd::name($device['hostname'], 'canopy-generic-whispGPSStats');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Value    1 = Synched   2 = Lost Sync    3 = Generating   \\n'";
    $rrd_options .= ' DEF:whispGPSStats=' . $rrdfilename . ':whispGPSStats:AVERAGE ';
    $rrd_options .= ' -l 1 ';
    $rrd_options .= ' -u 3 ';
    $rrd_options .= " LINE2:whispGPSStats#00B8E6:'GPS Status      ' ";
    $rrd_options .= ' GPRINT:whispGPSStats:LAST:%0.2lf%s\\\l ';
}
