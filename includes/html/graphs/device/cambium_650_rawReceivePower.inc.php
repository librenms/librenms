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

$rrdfilename = Rrd::name($device['hostname'], 'cambium-650-rawReceivePower');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                Now       Ave      Max     \\n'";
    $rrd_options .= ' DEF:rawReceivePower=' . $rrdfilename . ':rawReceivePower:AVERAGE ';
    $rrd_options .= " LINE2:rawReceivePower#00FF00:'Receive Power         ' ";
    $rrd_options .= ' GPRINT:rawReceivePower:LAST:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rawReceivePower:MIN:%0.2lf%s ';
    $rrd_options .= ' GPRINT:rawReceivePower:MAX:%0.2lf%s\\\l ';
}
