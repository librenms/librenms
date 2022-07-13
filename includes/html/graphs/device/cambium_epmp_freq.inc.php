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
$rrdfilename = Rrd::name($device['hostname'], 'cambium-epmp-freq');
if (Rrd::checkRrdExists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Mhz         \\n'";
    $rrd_options .= ' DEF:freq=' . $rrdfilename . ':freq:AVERAGE ';
    $rrd_options .= " LINE2:freq#008080:'Frequency  ' ";
    $rrd_options .= ' GPRINT:freq:LAST:%0.2lf%s ';
}
