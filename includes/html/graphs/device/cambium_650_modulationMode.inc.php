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

$rrdfilename = Rrd::name($device['hostname'], 'cambium-650-modulationMode');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Mode                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:rxModulation=' . $rrdfilename . ':rxModulation:AVERAGE ';
    $rrd_options .= ' DEF:txModulation=' . $rrdfilename . ':txModulation:AVERAGE ';
    $rrd_options .= ' -l 0 ';
    $rrd_options .= " LINE2:rxModulation#0000FF:'Receive Modulation         ' ";
    $rrd_options .= ' GPRINT:rxModulation:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rxModulation:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rxModulation:MAX:%0.2lf%s\\\l ';
    $rrd_options .= " LINE2:txModulation#FF0000:'Transmit Modulation        ' ";
    $rrd_options .= ' GPRINT:txModulation:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:txModulation:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:txModulation:MAX:%0.2lf%s\\\l ';
}
