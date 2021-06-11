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

$rrdfilename = Rrd::name($device['hostname'], 'cambium-250-transmitPower');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:transmitPower=' . $rrdfilename . ':transmitPower:AVERAGE ';
    $rrd_options .= " LINE2:transmitPower#FF0000:'Transmit Power         ' ";
    $rrd_options .= ' -l 0 ';
    $rrd_options .= ' GPRINT:transmitPower:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:transmitPower:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:transmitPower:MAX:%0.2lf%s\\\l ';
}
